<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client as Client;
use App\Config;
use Amp\Http\Server\HttpErrorException;
use function Amp\delay;
use Amp\Socket\ConnectException;
use Amp\Http\Client\SocketException;

class ProxyHandler implements RequestHandler
{
    
    public function __construct(
        private Config $config,
        private HttpClient $http,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        $uri = $request->getUri();
        $upUri = $uri->withHost($this->config->getUpstreamHost())->withPort($this->config->getUpstreamPort());
        
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