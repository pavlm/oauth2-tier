<?php
namespace App\OAuth\Providers;

use Kelunik\OAuth\Identity;
use Kelunik\OAuth\Provider;
use Amp\Http\Client\HttpClient;

class YandexProvider extends Provider
{

    protected string $authorizationUrl = 'https://oauth.yandex.ru/authorize';
    protected string $accessTokenUrl = 'https://oauth.yandex.ru/token';
    protected string $userInfoUrl = 'https://login.yandex.ru/info?format=json';
    
    public function __construct(
        HttpClient $httpClient,
        string $redirectUri,
        string $clientId,
        string $clientSecret,
        array $scopes = []
    ) {
        parent::__construct($httpClient, $redirectUri, $clientId, $clientSecret, \implode(' ', $scopes));
    }
    
    public function getName(): string
    {
        return 'Yandex';
    }

    public function getInternalName(): string
    {
        return 'yandex';
    }

    public function getIdentity(string $accessToken): Identity
    {
        
    }

}