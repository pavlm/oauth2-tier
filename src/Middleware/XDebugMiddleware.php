<?php
namespace App\Middleware;

use Amp\Http\Server\Middleware;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;

/**
 * Starts step debug if trigger event occurs
 */
class XDebugMiddleware implements Middleware
{
    
    const TRIGGER1 = 'XDEBUG_TRIGGER';
    const TRIGGER2 = 'XDEBUG_SESSION';
    
    private $loaded;
    private $check;
    
    public function __construct()
    {
        if ($this->loaded = extension_loaded('xdebug')) {
            $off = getenv('XDEBUG_MODE') == 'off' || ini_get('xdebug.mode') == 'off';
            $this->check = !$off;
        } else {
            $this->check = false;
        }
    }
    
    public function handleRequest(Request $request, RequestHandler $requestHandler): Response
    {
        if (!$this->loaded || !$this->check || xdebug_is_debugger_active()) {
            return $requestHandler->handleRequest($request);
        }
        
        $start = ini_get('xdebug.start_with_request') == 'yes' ||
            $this->isDebugRequested($request);
        if ($start) {
            xdebug_break();
        }
        return $requestHandler->handleRequest($request);
    }
    
    private function isDebugRequested(Request $request)
    {
        $cookie = $request->getCookie(self::TRIGGER1);
        if ($cookie?->getValue() === '' || $cookie?->getValue() != false) {
            return true;
        }
        $cookie = $request->getCookie(self::TRIGGER2);
        if ($cookie?->getValue() === '' || $cookie?->getValue() != false) {
            return true;
        }
        $param = $request->getQueryParameter(self::TRIGGER1);
        if ($param === '' || $param != false) {
            return true;
        }
        $param = $request->getQueryParameter(self::TRIGGER2);
        if ($param === '' || $param != false) {
            return true;
        }
    }
    
}