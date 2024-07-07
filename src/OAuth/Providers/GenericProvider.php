<?php
namespace App\OAuth\Providers;

use Kelunik\OAuth\Identity;
use Kelunik\OAuth\Provider;
use Amp\Http\Client\HttpClient;

class GenericProvider extends Provider
{
    
    public function __construct(
        HttpClient $httpClient,
        string $redirectUri,
        string $clientId,
        string $clientSecret,
        array $scopes = [],
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
        return 'generic';
    }

    public function getIdentity(string $accessToken): Identity
    {
        
    }

}