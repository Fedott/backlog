<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\DependencyInjection\Compiler;

use Fedot\Backlog\DependencyInjection\Compiler\SerializerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SerializerPassTest extends TestCase
{
    public function testThrowExceptionWhenNoNormalizers()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('serializer')
            ->will($this->returnValue(true))
        ;

        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('serializer.normalizer')
            ->will($this->returnValue([]))
        ;

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
            ->will($this->returnValue(true))
        ;

        $container->expects($this->any())
            ->method('findTaggedServiceIds')
            ->will(
                $this->onConsecutiveCalls(
                    ['n' => ['serializer.normalizer']],
                    []
                )
            )
        ;

        $container->expects($this->once())
            ->method('getDefinition')
            ->will($this->returnValue($definition))
        ;

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
            ->will($this->returnValue($services))
        ;

        $serializerPass = new SerializerPass();

        $method = new \ReflectionMethod(
            SerializerPass::class,
            'findAndSortTaggedServices'
        );
        $method->setAccessible(true);

        $actual = $method->invoke($serializerPass, 'tag', $container);

        $this->assertEquals($expected, $actual);
    }

    public function testServiceReplaceArguments()
    {
        $normalizers = [
            'n3' => ['tag' => []],
            'n1' => ['tag' => ['priority' => 200]],
            'n2' => ['tag' => ['priority' => 100]],
        ];
        $encoders = [
            'e3' => ['tag' => []],
            'e1' => ['tag' => ['priority' => 100]],
            'e2' => ['tag' => ['priority' => 50]],
        ];

        $expectedNormalizers = [
            new Reference('n1'),
            new Reference('n2'),
            new Reference('n3'),
        ];
        $expectedEncoders = [
            new Reference('e1'),
            new Reference('e2'),
            new Reference('e3'),
        ];

        $definition = $this->createMock(Definition::class);
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('serializer')
            ->willReturn(true)
        ;
        $container->expects($this->exactly(2))
            ->method('findTaggedServiceIds')
            ->withConsecutive(['serializer.normalizer'], ['serializer.encoder'])
            ->willReturnOnConsecutiveCalls($normalizers, $encoders)
        ;
        $container->expects($this->exactly(2))
            ->method('getDefinition')
            ->with('serializer')
            ->willReturn($definition)
        ;

        $definition->expects($this->exactly(2))
            ->method('replaceArgument')
            ->withConsecutive([0, $expectedNormalizers], [1, $expectedEncoders])
            ->willReturnSelf()
        ;

        $serializerPass = new SerializerPass();

        $serializerPass->process($container);
    }

    public function testHasNotDefinition()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('serializer')
            ->willReturn(false)
        ;

        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->never())->method('findTaggedServiceIds');

        $serializerPass = new SerializerPass();

        $serializerPass->process($container);
    }
}
