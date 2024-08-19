<?php
namespace App\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Session\Session;
use App\OAuth\IdentityData;
use Psr\Log\LoggerInterface;
use Amp\Http\Server\HttpErrorException;
use League\Uri\BaseUri;

class AuthMiddleware implements Middleware
{
    
    public const IDENTITY_ATTRIBUTE = 'identity';
    
    const PUBLIC_PATH = '#^(/ping|/oauth2/.*)$#';
    
    public function __construct(private LoggerInterface $logger)
    {
    }
    
    public function handleRequest(Request $request, RequestHandler $requestHandler): Response
    {
        /** @var Session $session */
        $session = $request->getAttribute(Session::class);
        
        /** @var ?IdentityData $identity */
        $identity = null;
        if ($session->getId() && !$session->isEmpty()) {
            $identity = $session->get(self::IDENTITY_ATTRIBUTE);
            $request->setAttribute(self::IDENTITY_ATTRIBUTE, $identity);
            $this->logger->info(json_encode(['identity' => print_r($identity, true)]));
        }
        
        if (!$identity) {
            if (!preg_match(self::PUBLIC_PATH, $request->getUri()->getPath())) {
                $root = BaseUri::from($request->getUri())->origin();
                $redirectUrl = '/' . $root->relativize($request->getUri())->getUriString();
                $link = sprintf('<a href="/oauth2/sign_in?redirect_url=%s">Login</a>', urlencode($redirectUrl));
                throw new HttpErrorException(401, 'Not authorized. ' . $link);
            }
        }
        
        $response = $requestHandler->handleRequest($request);
        return $response;
    }
    
    public static function getRequestUser(Request $request): ?IdentityData
    {
        return $request->hasAttribute(self::IDENTITY_ATTRIBUTE) ? $request->getAttribute(self::IDENTITY_ATTRIBUTE) : null;
    }
    
    public static function loginUser(Request $request, IdentityData $user)
    {
        /** @var Session $session */
        $session = $request->getAttribute(Session::class);
        $session->lock();
        $session->set(self::IDENTITY_ATTRIBUTE, $user);
        $session->commit();
    }
    
}