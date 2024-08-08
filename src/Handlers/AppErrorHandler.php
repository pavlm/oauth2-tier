<?php

namespace App\Handlers;

use Amp\Http\HttpStatus;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Request;
use function App\renderPhp;

final class AppErrorHandler implements ErrorHandler
{
    
    public function handleError(int $status, ?string $reason = null, ?Request $request = null): Response
    {
        $html = renderPhp(__DIR__ . '/views/error.php', ['code' => $status, 'reason' => $reason]);
        
        $response = new Response(
            headers: [
                "content-type" => "text/html; charset=utf-8",
            ],
            body: $html,
        );
        
        $response->setStatus($status, HttpStatus::getReason($status));
        
        return $response;
    }
}
