<?php
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use App\Application;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env', 'ENV_KEY');

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');
$container->compile(true);

$app = $container->get(Application::class);
$app->run();


