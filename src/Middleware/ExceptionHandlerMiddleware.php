<?php
namespace App\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpErrorException;
use Psr\Log\LoggerInterface;

/**
 * Error page with exception message
 */
class ExceptionHandlerMiddleware implements Middleware
{
    
    public function __construct(private ErrorHandler $errorHandler, private LoggerInterface $logger)
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
            $this->logServerError($request, $e);
            return $this->errorHandler->handleError(500, sprintf("#%d %s", $e->getCode(), $e->getMessage()));
        }
    }

    private function logServerError(Request $request, \Throwable $exception)
    {
        $client = $request->getClient();
        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $protocolVersion = $request->getProtocolVersion();
        $local = $client->getLocalAddress()->toString();
        $remote = $client->getRemoteAddress()->toString();
        
        $this->logger->error(
            \sprintf(
                "Unexpected %s with message '%s' thrown from %s:%d when handling request: %s %s HTTP/%s %s on %s",
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $method,
                $uri,
                $protocolVersion,
                $remote,
                $local,
            ),
            [
                'exception' => $exception,
                'request' => $request,
                'uri' => $uri,
                'protocolVersion' => $protocolVersion,
                'local' => $local,
                'remote' => $remote,
            ],
        );
    }
    
}