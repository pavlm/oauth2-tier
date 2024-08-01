<?php
namespace App\OAuth\Providers;

use Kelunik\OAuth\Identity;
use Kelunik\OAuth\Provider;
use Amp\Http\Client\HttpClient;
use App\OAuth\IdentityLoader;
use Amp\Http\Client\Request;
use Amp\Http\Client\HttpException;

class GenericProvider extends Provider
{
    
    public function __construct(
        protected HttpClient $httpClient,
        protected string $redirectUri,
        protected string $authorizationUrl,
        protected string $accessTokenUrl,
        protected string $userInfoUrl,
        protected string $clientId,
        protected string $clientSecret,
        protected IdentityLoader $loader,
        array $scopes = [],
        protected string $id = 'generic',
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
        return $this->id;
    }

    public function getIdentity(string $accessToken): Identity
    {
        $request = new Request($this->userInfoUrl, "POST", '');
        $request->setHeaders(['authorization' => 'Bearer ' . $accessToken]);
        $response = $this->httpClient->request($request);
        
        if ($response->getStatus() !== 200) {
            throw new HttpException('user info query failure (' . $response->getStatus() . ')');
        }
        
        $rawResponse = $response->getBody()->buffer();
        $response = \json_decode($rawResponse, true);
        
        $identity = $this->loader->create($this, $response);
        return $identity;
        
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }
    
    public function withRedirectUri(string $redirectUri): self
    {
        $c = clone $this;
        $c->redirectUri = $redirectUri;
        return $c;
    }

}