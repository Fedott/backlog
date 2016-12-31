<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\DependencyInjection\Compiler;

use Fedot\Backlog\DependencyInjection\Compiler\RequestProcessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tests\Fedot\Backlog\BaseTestCase;

class RequestProcessorPassTest extends BaseTestCase
{
    public function testServiceReplaceArguments()
    {
        $requestProcessors = [
            'rp3' => ['tag' => []],
            'rp1' => ['tag' => []],
            'rp2' => ['tag' => []],
        ];

        $expectedRequestProcessor1 = new Reference('rp3');
        $expectedRequestProcessor2 = new Reference('rp1');
        $expectedRequestProcessor3 = new Reference('rp2');

        $processorManagerDefinition = $this->createMock(Definition::class);
        $serializerServiceDefinition = $this->createMock(Definition::class);
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->exactly(2))
            ->method('hasDefinition')
            ->withConsecutive(['backlog.request.processor.manager'], ['backlog.serializer-service'])
            ->willReturn(true)
        ;
        $container->expects($this->exactly(2))
            ->method('getDefinition')
            ->withConsecutive(['backlog.request.processor.manager'], ['backlog.serializer-service'])
            ->willReturnOnConsecutiveCalls($processorManagerDefinition, $serializerServiceDefinition)
        ;
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('backlog.request.processor')
            ->willReturn($requestProcessors)
        ;

        $processorManagerDefinition->expects($this->exactly(3))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addProcessor', [$expectedRequestProcessor1]],
                ['addProcessor', [$expectedRequestProcessor2]],
                ['addProcessor', [$expectedRequestProcessor3]]
            )
        ;
        $serializerServiceDefinition->expects($this->exactly(3))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addPayloadTypeFromProcessor', [$expectedRequestProcessor1]],
                ['addPayloadTypeFromProcessor', [$expectedRequestProcessor2]],
                ['addPayloadTypeFromProcessor', [$expectedRequestProcessor3]]
            )
        ;

        $requestProcessorPass = new RequestProcessorPass();

        $requestProcessorPass->process($container);
    }

    public function testHasNotDefinitionProcessorManager()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('backlog.request.processor.manager')
            ->willReturn(false)
        ;

        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->never())->method('findTaggedServiceIds');

        $requestProcessorPass = new RequestProcessorPass();

        $requestProcessorPass->process($container);
    }

    public function testHasNotDefinitionSerializeService()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->exactly(2))
            ->method('hasDefinition')
            ->withConsecutive(['backlog.request.processor.manager'], ['backlog.serializer-service'])
            ->willReturnOnConsecutiveCalls(true, false)
        ;

        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->never())->method('findTaggedServiceIds');

        $requestProcessorPass = new RequestProcessorPass();

        $requestProcessorPass->process($container);
    }
}
