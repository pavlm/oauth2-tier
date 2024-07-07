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
use App\Handlers\PingRequestHandler;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Handlers\SignInRequestHandler;
use App\Handlers\CallbackRequestHandler;
use App\Handlers\StartRequestHandler;
use App\Handlers\ProxyHandler;
use App\Middleware\ExceptionHandlerMiddleware;

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
        
        print_r($this->config);
        var_export($_ENV);
        
        
        $errorHandler = new DefaultErrorHandler();
        $server = SocketHttpServer::createForDirectAccess($logger);
        $exceptionMiddleware = new ExceptionHandlerMiddleware($errorHandler);
        $router = new Router($server, $logger, $errorHandler);
        //$router->addMiddleware($exceptionMiddleware);
        $router->addRoute('GET', '/ping', $this->container->get(PingRequestHandler::class));
        $router->addRoute('GET', '/oauth2/sign_in', $this->container->get(SignInRequestHandler::class));
        $router->addRoute('GET', '/oauth2/start/{provider}', $this->container->get(StartRequestHandler::class));
        $router->addRoute('GET', '/oauth2/callback/{provider}', $this->container->get(CallbackRequestHandler::class));
        $router->setFallback($this->container->get(ProxyHandler::class));
        
        //$router->setFallback($requestHandler)
        $server->expose($this->config->httpAddress);
        $server->start($router, $errorHandler);
        
        // Serve requests until SIGINT or SIGTERM is received by the process.
        \Amp\trapSignal([
            SIGINT,
            SIGTERM
        ]);
        
        $server->stop();
        
    }
    
}