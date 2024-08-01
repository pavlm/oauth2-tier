<?php
namespace App\Net;

class IpFilter
{
    
    private $acceptCache = [];
    
    /**
     * @param array<IpBlock> $ipBlocks
     */
    public function __construct(private array $ipBlocks)
    {
    }
    
    public function check(string $ip): bool
    {
        $ipLong = ip2long($ip);
        if (isset($this->acceptCache[$ipLong])) {
            return true;
        }
        foreach ($this->ipBlocks as $block) {
            if ($block->includesLong($ipLong)) {
                $this->acceptCache[$ipLong] = 1;
                return true;
            }
        }
        return false;
    }
    
}