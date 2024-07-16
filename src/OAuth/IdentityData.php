<?php
namespace App\OAuth;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Serializable user identity
 */
#[Autoconfigure(autowire: false)]
class IdentityData
{
    
    public function __construct(
        private string $providerId,
        private string $id,
        private string $name,
        private ?string $avatar = null,
        private ?string $email = null,
    )
    {
    }
    
    public function getProviderId()
    {
        return $this->providerId;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getAvatar()
    {
        return $this->avatar;
    }
    
    public function getEmail()
    {
        return $this->email;
    }

    public static function convert(Identity $id): IdentityData
    {
        return new self($id->getProvider()->getInternalName(), $id->getId(), $id->getName(), $id->getAvatar(), $id->getEmail());
    }
    
}