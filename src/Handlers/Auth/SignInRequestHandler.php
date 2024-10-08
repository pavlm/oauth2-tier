<?php
namespace App\Handlers\Auth;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\OAuth\ProviderRegistry;
use function App\renderPhp;
use Amp\Http\Server\Session\Session;
use App\Middleware\AuthMiddleware;
use App\Config\Config;

class SignInRequestHandler implements RequestHandler
{
    
    public function __construct(
        private ProviderRegistry $registry,
        private Config $config,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        $providers = $this->registry->getList();
        
        $user = AuthMiddleware::getRequestUser($request);

        $html = renderPhp(__DIR__ . '/../views/signIn.php', [
            'providers' => $providers, 'user' => $user, 'request' => $request, 'pathPrefix' => $this->config->getUrlPathPrefix(),
        ]);
        
        return new Response(200, [
            "content-type" => "text/html; charset=utf-8"
        ], $html);
    }
    
}