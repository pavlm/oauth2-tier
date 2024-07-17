<?php
namespace App\Handlers;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;

class StaticRequestHandler implements RequestHandler
{
    
    public function __construct(
        private string $body = '',
        private string $contentType = '',
        private int $status = 200,
    )
    {
    }
    
    public function handleRequest(Request $request): Response
    {
        return new Response($this->status, array_filter(['content-type' => $this->contentType]), $this->body);
    }
    
}