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
    
    public function isExactLocation(): bool
    {
        return boolval($this->options['exactLocation'] ?? false);
    }
    
    public function getLocationRoute(): string
    {
        return match($this->handlerType) {
            HandlerType::Proxy, 
            HandlerType::Browser, 
            HandlerType::Statics => $this->location . '{_rest:.*}',
            HandlerType::Php => ($this->isExactLocation() ? $this->location : $this->location . '{_rest:.*}'),
        };
    }
    
    public static function createForConfig(array $config): self
    {
        return new self(
            $config[0],
            HandlerType::from($config[1]),
            $config[2] ?? null,
            $config[3] ?? null,
        );
    }
    
}
