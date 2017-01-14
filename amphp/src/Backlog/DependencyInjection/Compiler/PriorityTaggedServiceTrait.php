<?php declare(strict_types=1);
namespace Fedot\Backlog\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

trait PriorityTaggedServiceTrait
{
    /**
     * Finds all services with the given tag name and order them by their priority.
     *
     * @param string $tagName
     * @param ContainerBuilder $container
     *
     * @return Reference[]
     */
    private function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds($tagName);
        $queue = new \SplPriorityQueue();
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
                $queue->insert(new Reference($serviceId), $priority);
            }
        }

        return iterator_to_array($queue, false);
    }
}
