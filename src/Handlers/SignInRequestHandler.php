<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\OAuth\ProviderRegistry;
use function App\renderPhp;
use Amp\Http\Server\Session\Session;
use App\Middleware\AuthMiddleware;

class SignInRequestHandler implements RequestHandler
{
    
    public function __construct(
        private ProviderRegistry $registry,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        $providers = $this->registry->getList();
        
        $user = AuthMiddleware::getRequestUser($request);

        $html = renderPhp(__DIR__ . '/views/signIn.php', ['providers' => $providers, 'user' => $user, 'request' => $request]);
        
        return new Response(200, [
            "content-type" => "text/html; charset=utf-8"
        ], $html);
    }
    
}