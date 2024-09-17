<?php
namespace App\Handlers;

use App\Config\LocationConfig;

trait LocationHandlerTrait
{
    private ?LocationConfig $locationConfig = null;
    
    public function setLocationConfig(LocationConfig $locationConfig)
    {
        $this->locationConfig = $locationConfig;
    }
    
}