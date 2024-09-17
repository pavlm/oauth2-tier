<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client as Client;
use App\Config\Config;
use Amp\Http\Server\HttpErrorException;
use Amp\Socket\ConnectException;
use Amp\Http\Client\SocketException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use App\Config\LocationConfig;
use Psr\Http\Message\UriInterface;
use League\Uri\Http as Uri;

#[Autoconfigure(shared: false)]
class ProxyHandler implements RequestHandler, LocationHandler
{
    private ?LocationConfig $locationConfig = null;
    
    private ?UriInterface $targetUri = null;
    
    private bool $stripPathPrefix = true;
    
    public function __construct(
        private Config $config,
        private HttpClient $http,
    )
    {
    }
   
    public function setLocationConfig(LocationConfig $locationConfig)
    {
        $this->locationConfig = $locationConfig;
        $this->targetUri = Uri::new($locationConfig->target);
        $this->stripPathPrefix = $locationConfig?->options['stripPathPrefix'] ?? true;
    }
    
    public function handleRequest(Request $request): Response
    {
        $uri = $request->getUri();
        $upUri = $uri->withHost($this->targetUri->getHost())->withPort($this->targetUri->getPort());
        if ($this->stripPathPrefix) {
            $path = $uri->getPath();
            $pathPrefix = $this->config->getUrlPathPrefix();
            $pathPrefix = rtrim($pathPrefix . $this->locationConfig->location, '/');
            if (str_starts_with($path, $pathPrefix)) {
                $path = substr($path, strlen($pathPrefix));
                $upUri = $upUri->withPath($path);
            }
        }
        
        $uprequest = new Client\Request($upUri, $request->getMethod(), $request->getBody());
        try {
            $upreply = $this->http->request($uprequest);
        } catch (SocketException $e) {
            if ($e->getPrevious() instanceof ConnectException) {
                throw new HttpErrorException(502, $e->getMessage());
            }
            throw new HttpErrorException(500, $e->getMessage());
        } catch (\Exception $e) {
            throw new HttpErrorException(500, $e->getMessage());
        }
        
        return new Response(status: $upreply->getStatus(), headers: $upreply->getHeaders(), body: $upreply->getBody());
    }
    
}