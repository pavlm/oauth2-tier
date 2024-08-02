<?php
namespace App\Middleware;

class ForwardedData
{
    
    public bool $trustedForwarder = false;
    
    public ?string $for = null;
    public ?string $host = null;
    public ?string $proto = null;
    
    public function getHostName()
    {
        return str_contains($this->host, ':') ?
            explode(':', $this->host)[0] :
            $this->host;
    }
    
    public function getHostPort()
    {
        return str_contains($this->host, ':') ?
            explode(':', $this->host)[1] :
            ($this->proto == 'https' ? 443 : 80);
    }
    
}