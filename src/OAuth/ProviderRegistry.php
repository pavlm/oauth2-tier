<?php
namespace App\OAuth;

use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Config;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\OAuth\Providers\GenericProvider;
use Psr\Http\Message\UriInterface;
use League\Uri\Http as Uri;
use Amp\Http\Server\Request;
use Amp\Socket\InternetAddress;
use App\Net\IpFilter;

class ProviderRegistry
{
    
    private $providerByHostCache = [];
    
    private IpFilter $ipForwarderFilter;
    
    public function __construct(
        private Config $config,
        #[Autowire('@service_container')]
        private ContainerInterface $container,
        #[Autowire('%env(json:OA2T_PROVIDERS)%')]
        private array $providers,
    )
    {
        $this->ipForwarderFilter = new IpFilter($this->config->getTrustedForwarderBlocks());
    }
    
    public function getList(): array
    {
        return array_map(fn ($name) => $this->getByName($name), $this->providers);
    }
    
    public function getByName($name): GenericProvider
    {
        if (!in_array($name, $this->providers)) {
            throw new \Exception(printf('provider "%s" not configured', $name));
        }
        return $this->container->get($name);
    }
    
    /**
     * @param string $name
     * @param UriInterface $hostUri
     * @return GenericProvider
     */
    public function getByNameForHost($name, UriInterface $hostUri): GenericProvider
    {
        $hostRootUri = Uri::new()->withHost($hostUri->getHost())->withPort($hostUri->getPort())->withScheme($hostUri->getScheme()); // root site url
        
        $providerId = $name . $hostRootUri;
        if ($provider = $this->providerByHostCache[$providerId]) {
            return $provider;
        }
        $providerOrig = $this->getByName($name);
        $redirectUri = Uri::new($providerOrig->getRedirectUri())
            ->withScheme($hostRootUri->getScheme())
            ->withHost($hostRootUri->getHost())
            ->withPort($hostRootUri->getPort());
        $provider = $providerOrig->withRedirectUri($redirectUri);
        $this->providerByHostCache[$providerId] = $provider;
        return $provider;
    }
    
    public function getByNameForRequest($name, Request $request): GenericProvider
    {
        $isForwarded = $request->hasHeader('x-forwarded-for') && $request->hasHeader('x-forwarded-host') && $request->hasHeader('x-forwarded-proto');
        
        if (!$isForwarded) {
            return $this->getByNameForHost($name, $this->config->getHttpRootUrl());
        }
        assert($request->getClient()->getRemoteAddress() instanceof InternetAddress);
        $forwarderIp = $request->getClient()->getRemoteAddress()->getAddress();
        if ($this->ipForwarderFilter->check($forwarderIp)) { // trusted forwarder
            $host = $request->getHeader('x-forwarded-host');
            $port = null;
            if (str_contains($host, ':')) {
                [$host, $port] = explode(':', $host);
            }
            $proto = $request->getHeader('x-forwarded-proto');
            $originalHost = Uri::new()->withHost($host)->withPort($port)->withScheme($proto);
            return $this->getByNameForHost($name, $originalHost);
        } else {
            return $this->getByNameForHost($name, $this->config->getHttpRootUrl());
        }
    }
    
}
