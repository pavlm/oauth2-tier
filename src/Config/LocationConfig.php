<?php
namespace App\Config;

class LocationConfig
{
    
    public function __construct(
        public string $location,
        public HandlerType $handlerType,
        public ?string $target = null,
        public ?array $options = null,
    ) {
    }
    
}
