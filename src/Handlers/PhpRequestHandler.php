<?php
namespace App\Handlers;

use App\Config\Config;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\Config\LocationConfig;
use function App\renderPhp;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Amp\Http\Server\HttpErrorException;

#[Autoconfigure(shared: false)]
class PhpRequestHandler implements RequestHandler, LocationHandler
{

    private ?LocationConfig $locationConfig = null;
    
    private bool $dirTarget = false;

    public function __construct(
        private Config $config,
    ) {
    }
    
    public function setLocationConfig(LocationConfig $locationConfig)
    {
        $this->locationConfig = $locationConfig;
        if (!file_exists($locationConfig->target)) {
            throw new \Exception('target doesn\'t exist: ' . $locationConfig->target);
        }
        $this->dirTarget = is_dir($locationConfig->target);
    }
    
    public function handleRequest(Request $request): Response
    {
        if (!$this->dirTarget) {
            $body = renderPhp($this->locationConfig->target);
            return new Response(200, [], $body);
        }
    
        $uri = $request->getUri();
        $path = $uri->getPath();
        $pathPrefix = $this->config->getUrlPathPrefix();
        $pathPrefix = rtrim($pathPrefix . $this->locationConfig->location, '/');
        if (!str_starts_with($path, $pathPrefix)) {
            throw new \Exception('wrong url');
        }
        $path = substr($path, strlen($pathPrefix));
        $path = filterUrlPath($path);
        $filePath = $this->locationConfig->target . '/' . ltrim($path, '/');
        if (!file_exists($filePath)) {
            throw new HttpErrorException(404);
        }
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if ($ext !== 'php') {
            throw new HttpErrorException(403);
        }
        $body = renderPhp($filePath);
        return new Response(200, [], $body);
    }

}

function filterUrlPath($path)
{
    $path = implode('/', array_filter(explode('/', $path), fn ($seg) => $seg !== '..'));
    $path = preg_replace('#//+#', '/', $path);
    return $path;
}
