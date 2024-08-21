<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\Config;
use function App\renderPhp;

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
        $root = dirname(__DIR__, 2) . '/tmp';
        $browser = new FileBrowser($root);
        $browser->selectTarget($path);
        $browser->readTarget();
        
        $html = renderPhp(__DIR__ . '/views/fileBrowser.php', ['browser' => $browser]);
        
        return new Response(body: $html);
    }
    
}