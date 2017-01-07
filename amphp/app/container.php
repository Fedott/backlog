<?php declare(strict_types = 1);

use Fedot\Backlog\DependencyInjection\Compiler\MiddlewarePass;
use Fedot\Backlog\DependencyInjection\Compiler\PropertyInfoPass;
use Fedot\Backlog\DependencyInjection\Compiler\RequestProcessorPass;
use Fedot\Backlog\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$container = new ContainerBuilder();

$container->addCompilerPass(new MiddlewarePass());
$container->addCompilerPass(new SerializerPass());
$container->addCompilerPass(new PropertyInfoPass());
$container->addCompilerPass(new RequestProcessorPass());

$phpLoader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../app/config'));
$yamlLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../app/config'));

$resolver = new LoaderResolver([
    $phpLoader,
    $yamlLoader,
]);

$yamlLoader->load('services.yml');

return $container;
