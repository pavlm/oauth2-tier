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
        $body = $request->getBody()->buffer(null, 512);
        //fwrite(fopen('php://stderr', 'w'), $body);
        parse_str($body, $form);

        $providerId = $form['provider'] ?? null;
        if (!$providerId || !$provider = $this->registry->getByNameForRequest($providerId, $request)) {
            throw new HttpErrorException(400, 'Provider not found');
        }
        
        $url = $provider->getAuthorizationUrl('');
        
        return new Response(302, [
            'location' => $url,
        ]);
    }
    
}