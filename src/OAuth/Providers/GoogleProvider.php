<?php
namespace App\OAuth\Providers;

use Amp\Http\Client\HttpClient;
use App\OAuth\IdentityLoader;
use Psr\Log\LoggerInterface;

class GoogleProvider extends GenericProvider
{
    
    protected string $authorizationUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected string $accessTokenUrl = 'https://oauth2.googleapis.com/token';
    protected string $userInfoUrl = 'https://openidconnect.googleapis.com/v1/userinfo';
    
    public function __construct(
        HttpClient $httpClient,
        string $clientId,
        string $clientSecret,
        IdentityLoader $loader = new IdentityLoader('/sub', '/name', '/picture', '/email'),
        array $scopes = ['email', 'openid', 'profile'],
        string $id = 'google',
        string $name = 'Google',
        ?LoggerInterface $logger = null,
        bool $debug = false,
    ) {
        parent::__construct(
            httpClient:       $httpClient,
            redirectUri:      '/oauth2/callback/' . $id,
            authorizationUrl: $this->authorizationUrl,
            accessTokenUrl:   $this->accessTokenUrl,
            userInfoUrl:      $this->userInfoUrl,
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