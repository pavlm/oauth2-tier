<?php
namespace App;

use League\Uri\Uri;
use Psr\Http\Message\UriInterface;

class Config
{
    
    public string $httpAddress = '0.0.0.0:8088';
    
    public string $httpRootUrl = 'http://0.0.0.0:8088/';
    
    public string $postLoginUrl = '/';
    
    public string $upstream = 'http://127.0.0.1:80';
    
    public string|array $emailDomains = '*';
    
    public string|array $emailsAllowed = '*';

    public bool $cookieSecure = false;
    
    public string $cookieExpire = 'PT48H';
    
    public array $providers = [];
    
    public string $accessLog = './access.log';
    
    public string $appLog = './app.log';
    
    
    public function getPostLoginUrl(): UriInterface
    {
        return Uri::new($this->postLoginUrl);
    }
    
    public function getUpstreamHost()
    {
        return parse_url($this->upstream, PHP_URL_HOST);
    }
    
    public function getUpstreamPort()
    {
        $port = parse_url($this->upstream, PHP_URL_PORT);
        return is_numeric($port) ? $port : 80;
    }
    
    public function getEmailDomains(): array
    {
        if (is_array($this->emailDomains)) {
            return $this->emailDomains;
        }
        if ($this->emailDomains == '*') {
            return [];
        }
        return preg_split('#[\s;,]+#', $this->emailDomains);
    }
    
    public function getCookieExpireTime(): \DateTimeInterface
    {
        $now = new \DateTimeImmutable();
        return $now->add(new \DateInterval($this->cookieExpire));
    }
    
    public function getCookieMaxAge(): int
    {
        $age = new \DateInterval($this->cookieExpire);
        $d0 = new \DateTime('@0');
        return intval($d0->add($age)->format('U'));
    }
    
}