<?php
namespace App\OAuth;

use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Config;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\OAuth\Providers\GenericProvider;
use Psr\Http\Message\UriInterface;
use League\Uri\Http as Uri;
use Amp\Http\Server\Request;
use App\Middleware\ForwardedData;

class ProviderRegistry
{
    
    private $providerByHostCache = [];
    
    public function __construct(
        private Config $config,
        #[Autowire('@service_container')]
        private ContainerInterface $container,
        #[Autowire('%env(json:OA2T_PROVIDERS)%')]
        private array $providers,
    )
    {
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
        if ($provider = $this->providerByHostCache[$providerId] ?? null) {
            return $provider;
        }
        $providerOrig = $this->getByName($name);
        $redirectUri = Uri::new($providerOrig->getRedirectUri())
            ->withHost($hostRootUri->getHost())
            ->withPort($hostRootUri->getPort())
            ->withScheme($hostRootUri->getScheme());
        $provider = $providerOrig->withRedirectUri($redirectUri);
        $this->providerByHostCache[$providerId] = $provider;
        return $provider;
    }
    
    public function getByNameForRequest($name, Request $request): GenericProvider
    {
        /** @var ForwardedData $forwarded */
        $forwarded = $request->hasAttribute(ForwardedData::class) ? $request->getAttribute(ForwardedData::class) : null;
        
        if (!$forwarded) {
            return $this->getByNameForHost($name, $this->config->getHttpRootUrl());
        }
        $originalHost = Uri::new()->withHost($forwarded->getHostName())->withPort($forwarded->getHostPort())->withScheme($forwarded->proto);
        return $this->getByNameForHost($name, $originalHost);
    }
    
}
