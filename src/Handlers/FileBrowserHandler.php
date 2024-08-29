<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\Config;
use function App\renderPhp;
use Amp\ByteStream\ReadableResourceStream;
use Amp\Http\Server\HttpErrorException;

class FileBrowserHandler implements RequestHandler
{
    
    public function __construct(
        private Config $config,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        $path = $request->getUri()->getPath();
        $pathPrefix = $this->config->getUrlPathPrefix();
        if ($pathPrefix) { // remove prefix
            $prefix = substr($path, 0, strlen($pathPrefix));
            if ($prefix !== $pathPrefix) {
                throw new HttpErrorException(404, "Not found. Try service root page: <a href='{$pathPrefix}'>{$pathPrefix}</a>.");
            }
            $path = substr($path, strlen($pathPrefix));
        }
        $browser = new FileBrowser($this->config->indexDirectory);
        $browser->selectTarget($path);
        $browser->readTarget();
        
        if ($browser->directFileLink) {
            // stream file
            $file = fopen($browser->targetFile->path, 'r');
            $stream = new ReadableResourceStream($file);
            return new Response(body: $stream, headers: ['content-type' => 'application/octet-stream']);
        }

        $html = renderPhp(__DIR__ . '/views/fileBrowser.php', ['browser' => $browser, 'pathPrefix' => $pathPrefix]);

        return new Response(body: $html);
    }
    
}