<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use function App\responseWithRedirect;

class SignOutRequestHandler implements RequestHandler
{
    
    public function handleRequest(Request $request): Response
    {
        /** @var Session $session */
        $session = $request->getAttribute(Session::class);
        $session->lock();
        $session->destroy();
        
        return responseWithRedirect('/oauth2/sign_in');
    }
    
}