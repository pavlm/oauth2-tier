<?php
namespace App\Config;

use League\Uri\Http as Uri;
use Psr\Http\Message\UriInterface;
use App\Net\IpBlock;

class Config
{
    
    public string $httpAddress = '0.0.0.0:8088';
    
    public string $httpRootUrl = 'http://0.0.0.0:8088/';
    
    public string $postLoginUrl = '/';
    
    public array $locations = [
        ['/browser', 'browser', '/var/log'],
        ['/', 'proxy', 'http://172.17.0.1:80'],
    ];
    
    public array $accessControl = [
        '/' => true,
        '/public' => false,
    ];
    
    public string|array $emailDomains = '*';
    
    public string|array $emailsAllowed = '*';

    public bool $cookieSecure = false;
    
    public string $cookieExpire = 'PT48H';
    
    public array $providers = [];
    
    public string|array $trustedForwarders = '127.0.0.0/8,172.16.0.0/12,192.168.0.0/16';
    
    public string $accessLog = './access.log';
    
    public string $appLog = 'php://stdout';
    
    private $trustedForwarderBlocks;
    
    private $locationsCache;
    
    private $accessControlRulesCache;
    
    public function getHttpRootUrl(): UriInterface
    {
        return Uri::new($this->httpRootUrl);
    }
    
    public function getUrlPathPrefix(): string
    {
        return rtrim($this->getHttpRootUrl()->getPath(), '/');
    }
    
    public function getPostLoginUrl(): UriInterface
    {
        return Uri::new($this->postLoginUrl);
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
    
    public function getEmailsAllowed(): array
    {
        if (is_array($this->emailsAllowed)) {
            return $this->emailsAllowed;
        }
        if ($this->emailsAllowed == '*') {
            return [];
        }
        return preg_split('#[\s;,]+#', $this->emailsAllowed) ?: [];
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
    
    public function getTrustedForwarders(): array
    {
        return is_array($this->trustedForwarders) ? $this->trustedForwarders : array_filter(preg_split('#[\s;,]+#', $this->trustedForwarders));
    }
    
    public function getTrustedForwarderBlocks(): array
    {
        return $this->trustedForwarderBlocks ??= array_map(fn ($block) => IpBlock::createFromCidr($block), $this->getTrustedForwarders());
    }
    
    /**
     * @return LocationConfig[]
     */
    public function getLocations(): array
    {
        return $this->locationsCache ??= array_map(fn ($config) => LocationConfig::createForConfig($config), $this->locations);
    }
    
    /**
     * @return AccessControlRule[]
     */
    public function getAccessControlRules(): array
    {
        return $this->accessControlRulesCache ??= array_map(fn ($location, $auth) => new AccessControlRule($location, $auth), array_keys($this->accessControl), $this->accessControl);
    }
    
}