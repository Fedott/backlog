<?php declare(strict_types = 1);
namespace Fedot\Backlog\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MiddlewarePass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {
        if (!$container->has('backlog.infrastructure.runner-factory')) {
            return;
        }

        $middlewares = $this->findAndSortTaggedServices('backlog.middleware', $container);

        $definition = $container->getDefinition('backlog.infrastructure.runner-factory');
        $definition->setAutowired(false);
        $definition->setArguments([
            $middlewares
        ]);
    }
}
