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
use App\Config\Config;
use function App\responseWithRedirect;
use Amp\Http\Server\Session\Session;

class CallbackRequestHandler implements RequestHandler
{
    
    public function __construct(
        private Config $config,
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
        $provider = $this->registry->getByNameForRequest($providerId, $request);
        $code = $request->getQueryParameter('code');
        $token = $provider->exchangeAccessTokenForCode($code);
        $this->logger->info(
            'oauth token: {token}', ['token' => $token]);
        
        $identity = $provider->getIdentity($token);
        $idd = IdentityData::convert($identity);
        
        $this->access->checkUserAllowed($identity);
        
        AuthMiddleware::loginUser($request, $idd);
        $this->logger->info('oauth identity data: {id}', ['id' => var_export($idd, true)]);
        
        // select redirect url
        $redirectUrl = $this->config->postLoginUrl;
        if (!$redirectUrl) {
            /** @var Session $session */
            $session = $request->getAttribute(Session::class);
            $url = $session->has('redirectUrl') ? $session->get('redirectUrl') : ($this->config->getUrlPathPrefix() . '/');
            $isAbsolute = preg_match('#^\w+://#', $url);
            $redirectUrl = $isAbsolute ? ($this->config->getUrlPathPrefix() . '/') : $url;
        }
        
        return responseWithRedirect($redirectUrl);
    }
    
}