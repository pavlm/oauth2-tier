<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use App\OAuth\ProviderRegistry;
use Amp\Http\Server\HttpErrorException;

class StartRequestHandler implements RequestHandler
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
        if (!$provider = $this->registry->getByName($providerId)) {
            throw new HttpErrorException(400, 'Provider not found');
        }
        
        //$provider->getAuthorizationUrl($state)
        
        return new Response(200, [], "ok\n");
    }
    
}