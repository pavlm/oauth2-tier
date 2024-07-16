<?php
namespace App\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpErrorException;

/**
 * Error page with exception message
 */
class ExceptionHandlerMiddleware implements Middleware
{
    
    public function __construct(private ErrorHandler $errorHandler)
    {
    }
    
    public function handleRequest(Request $request, RequestHandler $requestHandler): Response
    {
        try {
            $response = $requestHandler->handleRequest($request);
            return $response;
        } catch (HttpErrorException $e) {
            return $this->errorHandler->handleError($e->getStatus(), sprintf("%s", $e->getMessage()));
        } catch (\Exception $e) {
            // @todo logging
            return $this->errorHandler->handleError(500, sprintf("#%d %s", $e->getCode(), $e->getMessage()));
        }
    }
    
}