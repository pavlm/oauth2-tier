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
use App\Config\Config;
use FastRoute\Dispatcher;

class AuthMiddleware implements Middleware
{
    
    public const IDENTITY_ATTRIBUTE = 'identity';
    
    private $pathPrefix = '';
    
    private ?Dispatcher $accessDispatcher = null;
    
    public function __construct(private LoggerInterface $logger, private Config $config)
    {
        $this->pathPrefix = $this->config->getUrlPathPrefix();
        $rules = $this->config->getAccessControlRules();
        if (empty($rules)) {
            throw new \Exception('No access control rules configured.');
        }
        $this->accessDispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $rc) {
            $rc->addRoute('*', $this->pathPrefix . '/oauth2/{tail:.*}', false);
            $rc->addRoute('*', $this->pathPrefix . '/ping', false);
            $rc->addRoute('*', '/oauth2/callback/{tail:.*}', false);
            foreach ($this->config->getAccessControlRules() as $rule) {
                $rc->addRoute('*', $this->pathPrefix . $rule->location . '{tail:.*}', $rule->auth);
            }
        });
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
            if ($identity) {
                $this->logger->info(json_encode(['identity' => $identity]));
            }
        }
        
        if (!$identity) {
            $path = $request->getUri()->getPath();
            [$result, $checkAccess] = $this->accessDispatcher->dispatch($request->getMethod(), $path);
            $notAuthorized = $result !== Dispatcher::FOUND;
            $notAuthorized |= $result == Dispatcher::FOUND && $checkAccess;
            if ($notAuthorized) {
                $root = BaseUri::from($request->getUri())->origin();
                $redirectUrl = '/' . $root->relativize($request->getUri())->getUriString();
                $link = sprintf('<a href="%s/oauth2/sign_in?redirect_url=%s">Login</a>', $this->pathPrefix, urlencode($redirectUrl));
                throw new HttpErrorException(401, $link);
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