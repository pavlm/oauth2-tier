<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\Config\Config;
use function App\renderPhp;
use Amp\ByteStream\ReadableResourceStream;
use Amp\Http\Server\HttpErrorException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(shared: false)]
class FileBrowserHandler implements RequestHandler, LocationHandler
{
    use LocationHandlerTrait;
    
    public function __construct(
        private Config $config,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        $path = $request->getUri()->getPath();
        $basePathPrefix = $this->config->getUrlPathPrefix();
        $pathPrefix = rtrim($basePathPrefix . $this->locationConfig->location, '/');
        if ($pathPrefix) { // remove prefix
            $prefix = substr($path, 0, strlen($pathPrefix));
            if ($prefix !== $pathPrefix) {
                throw new HttpErrorException(404, "Not found. Try service root page: <a href='{$pathPrefix}'>{$pathPrefix}</a>.");
            }
            $path = substr($path, strlen($pathPrefix));
        }
        $browser = new FileBrowser(rootDir: $this->locationConfig->target);
        $browser->selectTarget($path);
        $browser->readTarget();
        
        if ($browser->directFileLink) {
            // stream file
            $file = fopen($browser->targetFile->path, 'r');
            $stream = new ReadableResourceStream($file);
            return new Response(body: $stream, headers: ['content-type' => 'application/octet-stream']);
        }

        $html = renderPhp(__DIR__ . '/views/fileBrowser.php', ['browser' => $browser, 'pathPrefix' => $pathPrefix, 'basePathPrefix' => $basePathPrefix]);

        return new Response(body: $html);
    }
    
}