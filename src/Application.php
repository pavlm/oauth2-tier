<?php
namespace App;

use Amp\Log\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Amp\Log\ConsoleFormatter;
use Monolog\Logger;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\SocketHttpServer;
use Amp\Http\Server\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Handlers\SignInRequestHandler;
use App\Handlers\CallbackRequestHandler;
use App\Handlers\StartRequestHandler;
use App\Handlers\ProxyHandler;
use App\Middleware\ExceptionHandlerMiddleware;
use Amp\Http\Server\Session\SessionMiddleware;
use Amp\Http\Cookie\CookieAttributes;
use App\Middleware\AuthMiddleware;
use function Amp\Http\Server\Middleware\stackMiddleware;
use App\Handlers\StaticRequestHandler;

class Application
{
    
    public function __construct(
        private Config $config,
        #[Autowire('@service_container')]
        private ContainerInterface $container,
    )
    {
    }

    public function run()
    {
        $logHandler = new StreamHandler(\Amp\ByteStream\getStdout());
        $logHandler->pushProcessor(new PsrLogMessageProcessor());
        $logHandler->setFormatter(new ConsoleFormatter());
        
        $logger = new Logger('server');
        $logger->pushHandler($logHandler);
        $logger->info('starting...');
        $this->container->set('logger', $logger);
        
        print_r($this->config);
        var_export($_ENV);
        
        
        $errorHandler = new DefaultErrorHandler();
        $server = SocketHttpServer::createForDirectAccess($logger);
        $exceptionMiddleware = new ExceptionHandlerMiddleware($errorHandler);
        $router = new Router($server, $logger, $errorHandler);
        $middlewares = [
            $exceptionMiddleware,
            new SessionMiddleware(cookieAttributes: $this->container->get(CookieAttributes::class)),
            new AuthMiddleware($logger),
        ];
        array_map($router->addMiddleware(...), $middlewares);
        $router->addRoute('GET', '/ping', new StaticRequestHandler('pong'));
        $router->addRoute('GET', '/oauth2/sign_in', $this->container->get(SignInRequestHandler::class));
        $router->addRoute('POST', '/oauth2/start', $this->container->get(StartRequestHandler::class));
        $router->addRoute('GET', '/oauth2/callback/{provider}', $this->container->get(CallbackRequestHandler::class));
        
        $proxyHandler = $this->container->get(ProxyHandler::class);
        $router->setFallback(stackMiddleware($proxyHandler, ...$middlewares));
        
        $server->expose($this->config->httpAddress);
        $server->start($router, $errorHandler);
        
        \Amp\trapSignal([
            SIGINT,
            SIGTERM
        ]);
        
        $server->stop();
        
    }
    
}