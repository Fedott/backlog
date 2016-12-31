<?php declare(strict_types = 1);
namespace Fedot\Backlog\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PropertyInfoPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('property_info')) {
            return;
        }

        $definition = $container->getDefinition('property_info');

        $listExtractors = $this->findAndSortTaggedServices('property_info.list_extractor', $container);
        $definition->replaceArgument(0, $listExtractors);

        $typeExtractors = $this->findAndSortTaggedServices('property_info.type_extractor', $container);
        $definition->replaceArgument(1, $typeExtractors);

        $descriptionExtractors = $this->findAndSortTaggedServices('property_info.description_extractor', $container);
        $definition->replaceArgument(2, $descriptionExtractors);

        $accessExtractors = $this->findAndSortTaggedServices('property_info.access_extractor', $container);
        $definition->replaceArgument(3, $accessExtractors);
    }
}
