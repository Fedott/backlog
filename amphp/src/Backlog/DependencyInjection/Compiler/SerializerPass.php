<?php declare(strict_types=1);
namespace Fedot\Backlog\DependencyInjection\Compiler;

use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SerializerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('serializer')) {
            return;
        }

        $normalizers = $this->findAndSortTaggedServices('serializer.normalizer', $container);

        if (empty($normalizers)) {
            throw new RuntimeException('You must tag at least one service as "serializer.normalizer" to use the Serializer service');
        }

        $container->getDefinition('serializer')->replaceArgument(0, $normalizers);

        $encoders = $this->findAndSortTaggedServices('serializer.encoder', $container);

        if (empty($encoders)) {
            throw new RuntimeException('You must tag at least one service as "serializer.encoder" to use the Serializer service');
        }

        $container->getDefinition('serializer')->replaceArgument(1, $encoders);
    }
}
