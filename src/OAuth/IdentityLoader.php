<?php
namespace App\OAuth;

use function App\getViaPointer;
use Kelunik\OAuth\Provider;

/**
 * Identity factory, maps data to identity object with json pointer notation
 */
class IdentityLoader
{
    
    public function __construct(
        private string $idPointer,
        private string $namePointer,
        private ?string $avatarPointer,
        private ?string $emailPointer,
    )
    {
    }
    
    public function create(Provider $provider, array $data): Identity
    {
        $args = [
            'id' => getViaPointer($data, $this->idPointer),
            'name' => getViaPointer($data, $this->namePointer, ''),
            'avatar' => getViaPointer($data, $this->avatarPointer, ''),
            'email' => getViaPointer($data, $this->emailPointer, ''),
        ];
        return new Identity($provider, ...$args);
    }
    
}