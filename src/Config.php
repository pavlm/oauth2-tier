<?php
namespace App;

class Config
{
    
    public string $httpAddress = '0.0.0.0:8088';
    
    public string $httpRootUrl = 'http://0.0.0.0:8088/';
    
    public string $upstream = 'http://127.0.0.1:80';
    
    public string $emailDomains = '*';

    public bool $cookieSecure = false;
    
    public string $cookieExpire = 'PT48H';
    
    public array $providers = [];
    
    public string $accessLog = './access.log';
    
    public string $appLog = './app.log';
    
    
    public function getUpstreamHost()
    {
        return parse_url($this->upstream, PHP_URL_HOST);
    }
    
    public function getUpstreamPort()
    {
        $port = parse_url($this->upstream, PHP_URL_PORT);
        return is_numeric($port) ? $port : 80;
    }
}