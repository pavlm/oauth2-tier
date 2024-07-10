<?php
namespace App\OAuth\Providers;

use Kelunik\OAuth\Identity;
use Kelunik\OAuth\Provider;
use Amp\Http\Client\HttpClient;

class GenericProvider extends Provider
{
    
    public function __construct(
        protected HttpClient $httpClient,
        protected string $redirectUri,
        protected string $authorizationUrl,
        protected string $accessTokenUrl,
        protected string $clientId,
        protected string $clientSecret,
        array $scopes = [],
        protected string $nameId = 'generic',
        protected string $name = 'Generic',
    ) {
        parent::__construct($httpClient, $redirectUri, $clientId, $clientSecret, \implode(' ', $scopes));
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    public function getInternalName(): string
    {
        return $this->nameId;
    }

    public function getIdentity(string $accessToken): Identity
    {
        
    }

}