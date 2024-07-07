<?php
namespace App\OAuth;

use App\OAuth\Providers\YandexProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Config;
use App\OAuth\Providers\KeycloakProvider;
use Kelunik\OAuth\Provider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProviderRegistry
{
    
    const PROVIDERS = [
        'yandex' => YandexProvider::class,
        'keycloak' => KeycloakProvider::class,
    ];
    
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
    
    public function getByName($name): Provider
    {
        if (!in_array($name, $this->providers)) {
            throw new \Exception(printf('provider "%s" not configured', $name));
        }
        return $this->container->get($name);
    }
    
}
