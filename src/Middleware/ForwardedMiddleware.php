<?php
namespace App\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use App\Net\IpFilter;
use Psr\Log\LoggerInterface;

class ForwardedMiddleware implements Middleware
{
    private IpFilter $ipForwarderFilter;
    
    public function __construct(array $trustedForwarderBlocks, private LoggerInterface $logger)
    {
        $this->ipForwarderFilter = new IpFilter($trustedForwarderBlocks);
    }
    
    public function handleRequest(Request $request, RequestHandler $requestHandler): Response
    {
        if ($isForwarded = $request->hasHeader('x-forwarded-for') && $request->hasHeader('x-forwarded-host') && $request->hasHeader('x-forwarded-proto')) {
            $data = new ForwardedData();
            $clientIp = $request->getClient()->getRemoteAddress()->getAddress();
            $data->trustedForwarder = $this->ipForwarderFilter->check($clientIp);
            if (!$data->trustedForwarder) {
                $this->logger->warning('not trusted http forwarder: ' . $clientIp);
            }
            if ($isForwarded && $data->trustedForwarder) {
                $data->for = $request->getHeader('x-forwarded-for');
                $data->host = $request->getHeader('x-forwarded-host');
                $data->proto = $request->getHeader('x-forwarded-proto');
            }
            $request->setAttribute(ForwardedData::class, $data);
        }
        
        $response = $requestHandler->handleRequest($request);
        return $response;
    }
    
}