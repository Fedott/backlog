<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\DependencyInjection\Compiler;

use Fedot\Backlog\DependencyInjection\Compiler\PropertyInfoPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PropertyInfoPassTest extends \PHPUnit_Framework_TestCase
{
    public function testServicesAreOrderedAccordingToPriority()
    {
        $services = [
            'n3' => ['tag' => []],
            'n1' => ['tag' => ['priority' => 200]],
            'n2' => ['tag' => ['priority' => 100]],
        ];
        $expected = [
            new Reference('n1'),
            new Reference('n2'),
            new Reference('n3'),
        ];
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->any())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));
        $propertyInfoPass = new PropertyInfoPass();
        $method = new \ReflectionMethod(
            PropertyInfoPass::class,
            'findAndSortTaggedServices'
        );
        $method->setAccessible(true);
        $actual = $method->invoke($propertyInfoPass, 'tag', $container);
        $this->assertEquals($expected, $actual);
    }

    public function testReturningEmptyArrayWhenNoService()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->any())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue([]));

        $propertyInfoPass = new PropertyInfoPass();
        $method = new \ReflectionMethod(
            PropertyInfoPass::class,
            'findAndSortTaggedServices'
        );
        $method->setAccessible(true);
        $actual = $method->invoke($propertyInfoPass, 'tag', $container);
        $this->assertEquals([], $actual);
    }
}
