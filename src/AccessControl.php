<?php
namespace App;

use App\OAuth\Identity;
use Amp\Http\Server\HttpErrorException;

class AccessControl
{
    
    public function __construct(
        private Config $config,
    )
    {
    }
    
    public function checkUserAllowed(Identity $user)
    {
        if ($this->config->emailDomains !== '*') {
            $domains = $this->config->getEmailDomains();
            if ($user->getEmail() === null) {
                throw new HttpErrorException(403, 'No user email available.');
            }
            [, $domain] = explode('@', $user->getEmail());
            if (!in_array($domain, $domains)) {
                throw new HttpErrorException(403, 'Email domain is not allowed');
            }
        }
        
        if ($this->config->emailsAllowed !== '*') {
            // @todo
        }
    }
    
}