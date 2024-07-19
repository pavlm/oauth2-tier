<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use App\OAuth\ProviderRegistry;
use App\OAuth\IdentityData;
use Psr\Log\LoggerInterface;
use App\Middleware\AuthMiddleware;
use App\AccessControl;

class CallbackRequestHandler implements RequestHandler
{
    
    public function __construct(
        private ProviderRegistry $registry,
        private LoggerInterface $logger,
        private AccessControl $access,
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
        $this->logger->info(
            'oauth token: {token}', ['token' => $token]);
        
        $identity = $provider->getIdentity($token);
        $idd = IdentityData::convert($identity);
        
        $this->access->checkUserAllowed($identity);
        
        AuthMiddleware::loginUser($request, $idd);
        $this->logger->info('oauth identity data: {id}', ['id' => var_export($idd, true)]);
        
        return new Response(200, [], "ok\n");
    }
    
}