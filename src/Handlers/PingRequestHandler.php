<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;

class PingRequestHandler implements RequestHandler
{
    
    public function __construct()
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        return new Response(200, [], "pong\n");
    }
    
}