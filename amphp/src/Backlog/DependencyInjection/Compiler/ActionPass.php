<?php declare(strict_types = 1);
namespace Fedot\Backlog\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ActionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('backlog.action.manager')
            || !$container->hasDefinition('backlog.serializer-service')
        ) {
            return;
        }

        $actionProcessorDefinition = $container->getDefinition('backlog.action.manager');
        $serializerServiceDefinition = $container->getDefinition('backlog.serializer-service');

        $taggedServices = $container->findTaggedServiceIds('backlog.action');

        foreach ($taggedServices as $id => $tags) {
            $actionProcessorDefinition->addMethodCall('registerAction', [new Reference($id)]);
            $serializerServiceDefinition->addMethodCall('addPayloadTypeFromAction', [new Reference($id)]);
        }
    }
}
