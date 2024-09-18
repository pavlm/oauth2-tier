<?php
namespace App\Handlers\Auth;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use function App\responseWithRedirect;
use App\Config\Config;

class SignOutRequestHandler implements RequestHandler
{
    public function __construct(private Config $config)
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        /** @var Session $session */
        $session = $request->getAttribute(Session::class);
        $session->lock();
        $session->destroy();
        
        return responseWithRedirect($this->config->getUrlPathPrefix() . '/oauth2/sign_in');
    }
    
}