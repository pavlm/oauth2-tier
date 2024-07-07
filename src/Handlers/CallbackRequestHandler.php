<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;

class CallbackRequestHandler implements RequestHandler
{
    
    public function __construct()
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        $args = $request->getAttribute(Router::class);
        $providerId = $args['provider'];
        
        return new Response(200, [], "ok\n");
    }
    
}