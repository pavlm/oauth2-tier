<?php
namespace App\Config;

class AccessControlRule
{

    public function __construct(
        public string $location,
        public bool $auth,
    ) {
    }

}