<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use App\OAuth\ProviderRegistry;

class CallbackRequestHandler implements RequestHandler
{
    
    public function __construct(
        private ProviderRegistry $registry,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        $args = $request->getAttribute(Router::class);
        $providerId = $args['provider'];
        $provider = $this->registry->getByName($providerId);
        $code = $request->getQueryParameter('code');
        $token = $provider->exchangeAccessTokenForCode($code);

        fwrite(fopen('php://stderr', 'w'), json_encode(['token' => $token]));
        
        return new Response(200, [], "ok\n");
    }
    
}