<?php
namespace App\Net;

class IpBlock
{
    
    private int $netAddrLong;
    private int $netMaskLong;
    
    public function __construct(private string $netAddr, private string $netMask)
    {
        $this->netAddrLong = ip2long($netAddr);
        $this->netMaskLong = ip2long($netMask);
    }
    
    public function includes(string $ip): bool
    {
        return $this->includesLong(ip2long($ip));
    }
    
    public function includesLong(int $ip): bool
    {
        return $this->netAddrLong == ($ip & $this->netMaskLong);
    }
    
    public static function createFromCidr($cidrBlock): self
    {
        [$addr, $maskBits] = explode('/', $cidrBlock);
        if (!is_numeric($maskBits) || $maskBits > 32) {
            throw new \Exception('Wrong cidr format');
        }
        $ipmask = long2ip(~((1 << (32 - $maskBits)) - 1));
        return new self($addr, $ipmask);
    }
    
}