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
    use RequestHandlerTrait;

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
    
        $path = $this->getInternalPath($request->getUri());
        if (null === $path) {
            throw new \Exception('wrong url');
        }
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
