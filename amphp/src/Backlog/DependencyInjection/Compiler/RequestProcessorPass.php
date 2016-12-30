<?php declare(strict_types = 1);
namespace Fedot\Backlog\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RequestProcessorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('backlog.request.processor.manager')
            || !$container->has('backlog.serializer-service')
        ) {
            return;
        }

        $requestProcessorDefinition = $container->getDefinition('backlog.request.processor.manager');
        $serializerServiceDefinition = $container->getDefinition('backlog.serializer-service');

        $taggedServices = $container->findTaggedServiceIds('backlog.request.processor');

        foreach ($taggedServices as $id => $tags) {
            $requestProcessorDefinition->addMethodCall('addProcessor', [new Reference($id)]);
            $serializerServiceDefinition->addMethodCall('addPayloadTypeFromProcessor', [new Reference($id)]);
        }
    }
}
