<?php
namespace App\OAuth\Providers;

use Amp\Http\Client\HttpClient;
use App\OAuth\IdentityLoader;
use Psr\Log\LoggerInterface;

class GithubProvider extends GenericProvider
{

    protected string $authorizationUrl = 'https://github.com/login/oauth/authorize';
    protected string $accessTokenUrl = 'https://github.com/login/oauth/access_token';
    protected string $userInfoUrl = 'https://api.github.com/user';
    
    public function __construct(
        HttpClient $httpClient,
        string $clientId,
        string $clientSecret,
        IdentityLoader $loader = new IdentityLoader('/id', '/login', '/avatar_url', '/email'),
        array $scopes = ['user'],
        string $id = 'github',
        string $name = 'Github',
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct(
            httpClient:       $httpClient, 
            redirectUri:      '/oauth2/callback/github',
            authorizationUrl: $this->authorizationUrl,
            accessTokenUrl:   $this->accessTokenUrl,
            userInfoUrl:      $this->userInfoUrl,
            userInfoMethod:   'GET',
            clientId:         $clientId, 
            clientSecret:     $clientSecret,
            loader:           $loader,
            scopes:           $scopes,
            id:               $id,
            name:             $name,
            logger:           $logger,
        );
    }

}