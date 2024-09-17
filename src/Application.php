<?php
namespace App;

use App\Config\Config;
use Amp\Log\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Amp\Log\ConsoleFormatter;
use Monolog\Logger;
use App\Handlers\AppErrorHandler;
use Amp\Http\Server\SocketHttpServer;
use Amp\Http\Server\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Handlers\SignInRequestHandler;
use App\Handlers\CallbackRequestHandler;
use App\Handlers\StartRequestHandler;
use App\Middleware\ExceptionHandlerMiddleware;
use Amp\Http\Server\Session\SessionMiddleware;
use Amp\Http\Cookie\CookieAttributes;
use App\Middleware\AuthMiddleware;
use function Amp\Http\Server\Middleware\stackMiddleware;
use App\Handlers\ConstRequestHandler;
use App\Handlers\SignOutRequestHandler;
use Amp\Http\Server\Session\SessionFactory;
use Monolog\Formatter\JsonFormatter;
use App\Middleware\AccessLoggerMiddleware;
use Amp\ByteStream\WritableResourceStream;
use App\Middleware\ForwardedMiddleware;
use App\Middleware\XDebugMiddleware;
use App\Handlers\LocationHandler;

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
        $logHandler = new StreamHandler(new WritableResourceStream(fopen($this->config->appLog, 'a+')));
        $logHandler->pushProcessor(new PsrLogMessageProcessor());
        $logHandler->setFormatter(new ConsoleFormatter());
        //$logHandler->setFormatter(new JsonFormatter());
        
        $logger = new Logger('server');
        $logger->pushHandler($logHandler);
        $logger->info('starting...');
        $this->container->set('logger', $logger);
        
        fwrite(fopen('php://stderr', 'wb'), print_r($this->config, true));
        var_export($_ENV);
        
        $cookieAttributes = CookieAttributes::default()
            ->withPath('/')
            ->withMaxAge($this->config->getCookieMaxAge());
        if ($this->config->cookieSecure) {
            $cookieAttributes = $cookieAttributes->withSecure();
        }
        
        $errorHandler = new AppErrorHandler();
        $server = SocketHttpServer::createForDirectAccess($logger, enableCompression: true);
        $router = new Router($server, $logger, $errorHandler);
        $middlewares = [
            new XDebugMiddleware(),
            new ForwardedMiddleware($this->config->getTrustedForwarderBlocks(), $logger),
            new AccessLoggerMiddleware(new WritableResourceStream(fopen($this->config->accessLog, 'a'))),
            new ExceptionHandlerMiddleware($errorHandler, $logger),
            new SessionMiddleware(factory: $this->container->get(SessionFactory::class), cookieAttributes: $cookieAttributes),
            //new AuthMiddleware($logger, $this->config),
        ];
        array_map($router->addMiddleware(...), $middlewares);
        $pathPrefix = $this->config->getUrlPathPrefix();
        $router->addRoute('GET', $pathPrefix . '/ping', new ConstRequestHandler('pong'));
        $router->addRoute('GET', $pathPrefix . '/oauth2/sign_in', $this->container->get(SignInRequestHandler::class));
        $router->addRoute('POST', $pathPrefix . '/oauth2/sign_out', $this->container->get(SignOutRequestHandler::class));
        $router->addRoute('POST', $pathPrefix . '/oauth2/start', $this->container->get(StartRequestHandler::class));
        $router->addRoute('GET', $pathPrefix . '/oauth2/callback/{provider}', $this->container->get(CallbackRequestHandler::class));
        $router->addRoute('GET', $pathPrefix . '/oauth2/fail', new ConstRequestHandler('failure', '', 500));
        if ($pathPrefix) {
            $router->addRoute('GET', '/oauth2/callback/{provider}', $this->container->get(CallbackRequestHandler::class));
        }

        foreach ($this->config->getLocations() as $location) {
            $class = $location->handlerType->handlerClass();
            $handler = $this->container->get($class);
            assert($handler instanceof LocationHandler);
            $handler->setLocationConfig($location);
            $route = $location->location . '{_rest:.*}';
            $router->addRoute('*', $route, $handler);
        }
        
        $server->expose($this->config->httpAddress);
        $server->start($router, $errorHandler);
        
        \Amp\trapSignal([
            SIGINT,
            SIGTERM
        ]);
        
        $server->stop();
        
    }
    
}