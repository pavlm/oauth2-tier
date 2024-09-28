<?php
namespace App;

use App\Config\Config;
use App\OAuth\IdentityData;
use Amp\Http\Server\HttpErrorException;
use Psr\Log\LoggerInterface;

class AccessControl
{
    
    public function __construct(
        private Config $config,
        private LoggerInterface $logger,
    )
    {
    }
    
    public function checkUserAllowed(IdentityData $user)
    {
        if ($this->config->emailDomains !== '*') {
            $domains = $this->config->getEmailDomains();
            if ($user->email === null) {
                throw new HttpErrorException(403, 'No user email available.');
            }
            [, $domain] = explode('@', $user->email);
            if (!in_array($domain, $domains)) {
                $this->logger->info(json_encode(['identity' => $user]));
                throw new HttpErrorException(403, 'Email domain is not allowed');
            }
        }
        
        if ($this->config->emailsAllowed !== '*') {
            if ($user->email === null) {
                throw new HttpErrorException(403, 'No user email available.');
            }
            $emails = array_map(strtolower(...), $this->config->getEmailsAllowed());
            $email = strtolower($user->email);
            if (!in_array($email, $emails)) {
                $this->logger->info(json_encode(['identity' => $user]));
                throw new HttpErrorException(403, 'Email address is not allowed');
            }
        }
    }
    
}