<?php
namespace App\OAuth;

use Kelunik\OAuth\Identity as BaseIdentity;
use Kelunik\OAuth\Provider;

class Identity extends BaseIdentity
{
    
    private ?string $email;
    
    public function __construct(Provider $provider, string $id, string $name, string $avatar, ?string $email)
    {
        parent::__construct($provider, $id, $name, $avatar);
        $this->email = $email;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
}