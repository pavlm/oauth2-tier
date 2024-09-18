<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\Config\Config;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use App\Config\LocationConfig;
use Amp\Http\Server\HttpErrorException;
use Mimey\MimeTypes;
use Amp\ByteStream\ReadableResourceStream;

#[Autoconfigure(shared: false)]
class StaticRequestHandler implements RequestHandler, LocationHandler
{
    use RequestHandlerTrait;
    
    private ?LocationConfig $locationConfig = null;
    
    public function __construct(
        private Config $config,
        private MimeTypes $mimes,
    ) {
    }
    
    public function setLocationConfig(LocationConfig $locationConfig)
    {
        $this->locationConfig = $locationConfig;
        if (!is_dir($locationConfig->target)) {
            throw new \Exception('target directory doesn\'t exist: ' . $locationConfig->target);
        }
    }
    
    public function handleRequest(Request $request): Response
    {
        $path = $this->getInternalPath($request->getUri());
        if (null === $path) {
            throw new HttpErrorException(404);
        }
        $filePath = $this->locationConfig->target . '/' . ltrim($path, '/');
        if (!file_exists($filePath)) {
            throw new HttpErrorException(404);
        }
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mime = $this->mimes->getMimeType($ext) ?? 'appilcation/octet-stream';
        $file = fopen($filePath, 'r');
        $stream = new ReadableResourceStream($file);
        return new Response(body: $stream, headers: ['content-type' => $mime]);
    }
    
}