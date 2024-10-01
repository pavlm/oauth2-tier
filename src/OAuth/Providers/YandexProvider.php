<?php
namespace App\OAuth\Providers;

use Amp\Http\Client\HttpClient;
use App\OAuth\IdentityLoader;
use Psr\Log\LoggerInterface;

class YandexProvider extends GenericProvider
{

    protected string $authorizationUrl = 'https://oauth.yandex.ru/authorize';
    protected string $accessTokenUrl = 'https://oauth.yandex.ru/token';
    protected string $userInfoUrl = 'https://login.yandex.ru/info?format=json';
    
    public function __construct(
        HttpClient $httpClient,
        string $clientId,
        string $clientSecret,
        IdentityLoader $loader = new IdentityLoader('/id', '/real_name', '/avatar', '/default_email'),
        array $scopes = ['login:info', 'login:email', 'login:avatar'],
        string $id = 'yandex',
        string $name = 'Yandex',
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct(
            httpClient:       $httpClient, 
            redirectUri:      '/oauth2/callback/yandex',
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
        );
    }

}