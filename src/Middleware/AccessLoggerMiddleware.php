<?php

namespace App\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\ByteStream\WritableStream;
use Amp\Socket\SocketAddressType;

final class AccessLoggerMiddleware implements Middleware
{

    private array $fields = [
        'time',
        'ip',
        'user',
        'method',
        'uri',
        'protocolVersion',
        'status',
    ];
    
    public function __construct(
        private WritableStream $stream,
        $fields = [],
    ) {
        $this->fields = $fields ?: $this->fields;
    }
    
    public function handleRequest(Request $request, RequestHandler $requestHandler): Response
    {
        $time = new \DateTime();
        $response = $requestHandler->handleRequest($request);
        
        $user = AuthMiddleware::getRequestUser($request);
        /** @var ForwardedData $forwardedData */
        $forwardedData = $request->hasAttribute(ForwardedData::class) ? $request->getAttribute(ForwardedData::class) : null;
        assert($request->getClient()->getRemoteAddress()->getType() == SocketAddressType::Internet);
        $clientIp = $forwardedData?->trustedForwarder ? $forwardedData->getFirstAddress() : $request->getClient()->getRemoteAddress()->getAddress();
        
        $record = [];
        foreach ($this->fields as $field) {
            $record[$field] = match ($field) {
                'time' => $time->format(\DateTime::ISO8601),
                'ip' => $clientIp,
                'user' => $user ? ($user->email ?: $user->name) : null,
                'method' => $request->getMethod(),
                'uri' => (string)$request->getUri(),
                'protocolVersion' => $request->getProtocolVersion(),
                'status' => $response->getStatus(),
            };
        }
        $this->stream->write(json_encode($record) . "\n");
        
        return $response;
    }
}
