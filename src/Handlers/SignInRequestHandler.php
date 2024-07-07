<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\OAuth\ProviderRegistry;
use function App\renderPhp;

class SignInRequestHandler implements RequestHandler
{
    
    public function __construct(
        private ProviderRegistry $registry,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        //$html = file_get_contents(__DIR__ . '/views/page.html');
        
        $providers = $this->registry->getList();

        $html = renderPhp(__DIR__ . '/views/signIn.php', ['providers' => $providers]);
        
        return new Response(200, [
            "content-type" => "text/html; charset=utf-8"
        ], $html);
    }
    
}