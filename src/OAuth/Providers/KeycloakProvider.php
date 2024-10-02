<?php
namespace App\OAuth\Providers;

use Amp\Http\Client\HttpClient;
use App\OAuth\IdentityLoader;
use Psr\Log\LoggerInterface;

class KeycloakProvider extends GenericProvider
{

    public function __construct(
        HttpClient $httpClient,
        protected string $realmUrl,
        string $clientId,
        string $clientSecret,
        IdentityLoader $loader = new IdentityLoader('/sub', '/name', '/avatar', '/email'),
        array $scopes = ['openid', 'profile', 'email'],
        string $id = 'keycloak',
        string $name = 'Keycloak',
        ?LoggerInterface $logger = null,
        bool $debug = false,
    ) {
        parent::__construct(
            httpClient:       $httpClient, 
            redirectUri:      '/oauth2/callback/keycloak', 
            authorizationUrl: $realmUrl . '/protocol/openid-connect/auth',
            accessTokenUrl:   $realmUrl . '/protocol/openid-connect/token',
            userInfoUrl:      $realmUrl . '/protocol/openid-connect/userinfo',
            clientId:         $clientId, 
            clientSecret:     $clientSecret,
            loader:           $loader,
            scopes:           $scopes,
            id:               $id,
            name:             $name,
            logger:           $logger,
            debug:            $debug,
        );
    }
    
}