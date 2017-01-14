<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\DependencyInjection\Compiler;

use Fedot\Backlog\DependencyInjection\Compiler\ActionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tests\Fedot\Backlog\BaseTestCase;

class ActionPassTest extends BaseTestCase
{
    public function testServiceReplaceArguments()
    {
        $actions = [
            'rp3' => ['tag' => []],
            'rp1' => ['tag' => []],
            'rp2' => ['tag' => []],
        ];

        $expectedAction1 = new Reference('rp3');
        $expectedAction2 = new Reference('rp1');
        $expectedAction3 = new Reference('rp2');

        $actionManagerDefinition = $this->createMock(Definition::class);
        $serializerServiceDefinition = $this->createMock(Definition::class);
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->exactly(2))
            ->method('hasDefinition')
            ->withConsecutive(['backlog.action.manager'], ['backlog.serializer-service'])
            ->willReturn(true)
        ;
        $container->expects($this->exactly(2))
            ->method('getDefinition')
            ->withConsecutive(['backlog.action.manager'], ['backlog.serializer-service'])
            ->willReturnOnConsecutiveCalls($actionManagerDefinition, $serializerServiceDefinition)
        ;
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('backlog.action')
            ->willReturn($actions)
        ;

        $actionManagerDefinition->expects($this->exactly(3))
            ->method('addMethodCall')
            ->withConsecutive(
                ['registerAction', [$expectedAction1]],
                ['registerAction', [$expectedAction2]],
                ['registerAction', [$expectedAction3]]
            )
        ;
        $serializerServiceDefinition->expects($this->exactly(3))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addPayloadTypeFromAction', [$expectedAction1]],
                ['addPayloadTypeFromAction', [$expectedAction2]],
                ['addPayloadTypeFromAction', [$expectedAction3]]
            )
        ;

        $actionPass = new ActionPass();

        $actionPass->process($container);
    }

    public function testHasNotDefinitionActionManager()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('backlog.action.manager')
            ->willReturn(false)
        ;

        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->never())->method('findTaggedServiceIds');

        $actionPass = new ActionPass();

        $actionPass->process($container);
    }

    public function testHasNotDefinitionSerializeService()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->exactly(2))
            ->method('hasDefinition')
            ->withConsecutive(['backlog.action.manager'], ['backlog.serializer-service'])
            ->willReturnOnConsecutiveCalls(true, false)
        ;

        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->never())->method('findTaggedServiceIds');

        $actionPass = new ActionPass();

        $actionPass->process($container);
    }
}
