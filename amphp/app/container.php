<?php declare(strict_types = 1);

use Fedot\Backlog\DependencyInjection\Compiler\MiddlewarePass;
use Fedot\Backlog\DependencyInjection\Compiler\PropertyInfoPass;
use Fedot\Backlog\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$container = new ContainerBuilder();

$container->addCompilerPass(new MiddlewarePass());
$container->addCompilerPass(new SerializerPass());
$container->addCompilerPass(new PropertyInfoPass());

$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../app'));
$loader->load('services.yml');

return $container;
