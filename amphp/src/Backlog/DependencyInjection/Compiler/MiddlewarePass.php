<?php declare(strict_types = 1);
namespace Fedot\Backlog\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MiddlewarePass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('backlog.infrastructure.runner-factory')) {
            return;
        }

        $definition = $container->getDefinition('backlog.infrastructure.runner-factory');
        $taggedServices = $container->findTaggedServiceIds('backlog.middleware');

        $prioritiesMiddlewares = [];
        foreach ($taggedServices as $id => $tag) {
            foreach ($tag as $attributes) {
                $prioritiesMiddlewares[$attributes['priority']][] = new Reference($id);
            }
        }

        $middlewares = [];
        foreach ($prioritiesMiddlewares as $priorityMiddleware) {
            foreach ($priorityMiddleware as $middleware) {
                $middlewares[] = $middleware;
            }
        }

        $definition->setAutowired(false);
        $definition->setArguments([
            $middlewares
        ]);
    }
}
