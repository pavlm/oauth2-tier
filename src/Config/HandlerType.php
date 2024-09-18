<?php
namespace App\Config;

use App\Handlers\ProxyHandler;
use App\Handlers\FileBrowserHandler;
use App\Handlers\PhpRequestHandler;
use App\Handlers\StaticRequestHandler;

enum HandlerType: string
{
    case Proxy = 'proxy';     // http upstream
    case Browser = 'browser'; // file browser with viewer
    case Statics = 'statics'; // file server
    case Php = 'php';         // php script runner
    
    public function handlerClass(): string
    {
        return match($this) {
            static::Proxy => ProxyHandler::class,
            static::Browser => FileBrowserHandler::class,
            static::Statics => StaticRequestHandler::class,
            static::Php => PhpRequestHandler::class,
        };
    }
    
}