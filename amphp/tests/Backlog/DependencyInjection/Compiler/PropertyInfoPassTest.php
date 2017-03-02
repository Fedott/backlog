<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\DependencyInjection\Compiler;

use Fedot\Backlog\DependencyInjection\Compiler\PropertyInfoPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PropertyInfoPassTest extends TestCase
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
            ->will($this->returnValue($services))
        ;
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
            ->will($this->returnValue([]))
        ;

        $propertyInfoPass = new PropertyInfoPass();
        $method = new \ReflectionMethod(
            PropertyInfoPass::class,
            'findAndSortTaggedServices'
        );
        $method->setAccessible(true);
        $actual = $method->invoke($propertyInfoPass, 'tag', $container);
        $this->assertEquals([], $actual);
    }

    public function testServiceReplaceArguments()
    {
        $listExtractors = [
            'l3' => ['tag' => []],
            'l1' => ['tag' => ['priority' => 200]],
            'l2' => ['tag' => ['priority' => 100]],
        ];
        $typeExtractors = [
            't3' => ['tag' => []],
            't1' => ['tag' => ['priority' => 100]],
            't2' => ['tag' => ['priority' => 50]],
        ];
        $descriptionExtractors = [
            'd3' => ['tag' => []],
            'd1' => ['tag' => ['priority' => 200]],
            'd2' => ['tag' => ['priority' => 100]],
        ];
        $accessExtractors = [
            'a3' => ['tag' => []],
            'a1' => ['tag' => ['priority' => 100]],
            'a2' => ['tag' => ['priority' => 50]],
        ];

        $expectedListExtractors = [
            new Reference('l1'),
            new Reference('l2'),
            new Reference('l3'),
        ];
        $expectedTypeExtractors = [
            new Reference('t1'),
            new Reference('t2'),
            new Reference('t3'),
        ];
        $expectedDescriptionExtractors = [
            new Reference('d1'),
            new Reference('d2'),
            new Reference('d3'),
        ];
        $expectedAccessExtractors = [
            new Reference('a1'),
            new Reference('a2'),
            new Reference('a3'),
        ];

        $definition = $this->createMock(Definition::class);
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('property_info')
            ->willReturn(true)
        ;
        $container->expects($this->exactly(1))
            ->method('getDefinition')
            ->with('property_info')
            ->willReturn($definition)
        ;
        $container->expects($this->exactly(4))
            ->method('findTaggedServiceIds')
            ->withConsecutive(
                ['property_info.list_extractor'],
                ['property_info.type_extractor'],
                ['property_info.description_extractor'],
                ['property_info.access_extractor']
            )
            ->willReturnOnConsecutiveCalls(
                $listExtractors,
                $typeExtractors,
                $descriptionExtractors,
                $accessExtractors
            )
        ;

        $definition->expects($this->exactly(4))
            ->method('replaceArgument')
            ->withConsecutive(
                [0, $expectedListExtractors],
                [1, $expectedTypeExtractors],
                [2, $expectedDescriptionExtractors],
                [3, $expectedAccessExtractors]
            )
            ->willReturnSelf()
        ;

        $propertyInfoPass = new PropertyInfoPass();

        $propertyInfoPass->process($container);
    }

    public function testHasNotDefinition()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('property_info')
            ->willReturn(false)
        ;

        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->never())->method('findTaggedServiceIds');

        $propertyInfoPass = new PropertyInfoPass();

        $propertyInfoPass->process($container);
    }
}
