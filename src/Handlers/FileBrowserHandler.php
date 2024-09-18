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
    use RequestHandlerTrait;
    
    public function __construct(
        private Config $config,
    ) {
    }
    
    public function handleRequest(Request $request): Response
    {
        $pathPrefix = $this->getLocationPathPrefix();
        $path = $this->getInternalPath($request->getUri());
        if (null === $path) {
            throw new HttpErrorException(404, "Not found. Try service root page: <a href='{$pathPrefix}'>{$pathPrefix}</a>.");
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

        $html = renderPhp(__DIR__ . '/views/fileBrowser.php', ['browser' => $browser, 'pathPrefix' => $pathPrefix, 'basePathPrefix' => $this->config->getUrlPathPrefix()]);

        return new Response(body: $html);
    }
    
}