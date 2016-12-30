<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\DependencyInjection\Compiler;

use Fedot\Backlog\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SerializerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowExceptionWhenNoNormalizers()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('serializer')
            ->will($this->returnValue(true));
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('serializer.normalizer')
            ->will($this->returnValue([]));
        $this->expectException('RuntimeException');
        $serializerPass = new SerializerPass();
        $serializerPass->process($container);
    }

    public function testThrowExceptionWhenNoEncoders()
    {
        $definition = $this->createMock(Definition::class);
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('serializer')
            ->will($this->returnValue(true));
        $container->expects($this->any())
            ->method('findTaggedServiceIds')
            ->will($this->onConsecutiveCalls(
                ['n' => ['serializer.normalizer']],
                []
            ));
        $container->expects($this->once())
            ->method('getDefinition')
            ->will($this->returnValue($definition));
        $this->expectException('RuntimeException');
        $serializerPass = new SerializerPass();
        $serializerPass->process($container);
    }

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
        $container->expects($this->any())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($services));
        $serializerPass = new SerializerPass();
        $method = new \ReflectionMethod(
            SerializerPass::class,
            'findAndSortTaggedServices'
        );
        $method->setAccessible(true);
        $actual = $method->invoke($serializerPass, 'tag', $container);
        $this->assertEquals($expected, $actual);
    }
}
