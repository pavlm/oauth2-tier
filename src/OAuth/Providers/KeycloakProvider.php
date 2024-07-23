<?php
namespace App\OAuth\Providers;

use Amp\Http\Client\HttpClient;
use App\OAuth\IdentityLoader;

class KeycloakProvider extends GenericProvider
{

    public function __construct(
        protected HttpClient $httpClient,
        protected string $redirectUri,
        protected string $realmUrl,
        protected string $clientId,
        protected string $clientSecret,
        protected IdentityLoader $loader = new IdentityLoader('/sub', '/name', '/avatar', '/email'),
        array $scopes = ['openid', 'profile', 'email'],
        protected string $id = 'keycloak',
        protected string $name = 'Keycloak',
    ) {
        parent::__construct(
            $httpClient, 
            $redirectUri, 
            $realmUrl . '/protocol/openid-connect/auth',
            $realmUrl . '/protocol/openid-connect/token',
            $realmUrl . '/protocol/openid-connect/userinfo',
            $clientId, 
            $clientSecret,
            $loader,
            $scopes,
            $id,
            $name,
        );
    }
    
}