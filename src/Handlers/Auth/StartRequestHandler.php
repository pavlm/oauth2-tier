<?php
namespace App\Handlers\Auth;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\OAuth\ProviderRegistry;
use Amp\Http\Server\HttpErrorException;
use Amp\Http\Server\Session\Session;

class StartRequestHandler implements RequestHandler
{
    
    public function __construct(
        private ProviderRegistry $registry,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        $body = $request->getBody()->buffer(null, 512);
        //fwrite(fopen('php://stderr', 'w'), $body);
        parse_str($body, $form);

        $providerId = $form['provider'] ?? null;
        if (!$providerId || !$provider = $this->registry->getByNameForRequest($providerId, $request)) {
            throw new HttpErrorException(400, 'Provider not found');
        }
        $redirectUrl = $form['redirect_url'] ?? null;
        if ($redirectUrl) {
            /** @var Session $session */
            $session = $request->getAttribute(Session::class);
            $session->lock();
            $session->set('redirectUrl', $redirectUrl);
            $session->commit();
        }
            
        $url = $provider->getAuthorizationUrl('');
        
        return new Response(302, [
            'location' => $url,
        ]);
    }
    
}