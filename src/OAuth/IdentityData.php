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
        public readonly string $providerId,
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $avatar = null,
        public readonly ?string $email = null,
    )
    {
    }

    public static function convert(Identity $id): IdentityData
    {
        return new self($id->getProvider()->getInternalName(), $id->getId(), $id->getName(), $id->getAvatar(), $id->getEmail());
    }
    
}