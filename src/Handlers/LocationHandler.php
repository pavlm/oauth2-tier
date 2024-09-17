<?php
namespace App\Handlers;

use App\Config\LocationConfig;

interface LocationHandler
{

    public function setLocationConfig(LocationConfig $locationConfig);

}