<?php
namespace App\OAuth\Providers;

use Amp\Http\Client\HttpClient;
use App\OAuth\IdentityLoader;

class GoogleProvider extends GenericProvider
{
    
    protected string $authorizationUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected string $accessTokenUrl = 'https://oauth2.googleapis.com/token';
    protected string $userInfoUrl = 'https://openidconnect.googleapis.com/v1/userinfo';
    
    public function __construct(
        HttpClient $httpClient,
        string $clientId,
        string $clientSecret,
        protected IdentityLoader $loader = new IdentityLoader('/sub', '/name', '/picture', '/email'),
        array $scopes = ['email', 'openid', 'profile'],
        protected string $id = 'google',
        protected string $name = 'Google',
    ) {
        parent::__construct(
            $httpClient,
            '/oauth2/callback/' . $id,
            $this->authorizationUrl,
            $this->accessTokenUrl,
            $this->userInfoUrl,
            $clientId,
            $clientSecret,
            $loader,
            $scopes,
            $id,
            $name,
        );
    }

}