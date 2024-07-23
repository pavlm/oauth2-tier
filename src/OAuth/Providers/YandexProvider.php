<?php
namespace App\OAuth\Providers;

use Amp\Http\Client\HttpClient;
use App\OAuth\IdentityLoader;

class YandexProvider extends GenericProvider
{

    protected string $authorizationUrl = 'https://oauth.yandex.ru/authorize';
    protected string $accessTokenUrl = 'https://oauth.yandex.ru/token';
    protected string $userInfoUrl = 'https://login.yandex.ru/info?format=json';
    
    public function __construct(
        HttpClient $httpClient,
        string $redirectUri,
        string $clientId,
        string $clientSecret,
        protected IdentityLoader $loader = new IdentityLoader('/id', '/real_name', '/avatar', '/default_email'),
        array $scopes = ['login:info', 'login:email', 'login:avatar'],
        protected string $id = 'yandex',
        protected string $name = 'Yandex',
    ) {
        parent::__construct(
            $httpClient, 
            $redirectUri,
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