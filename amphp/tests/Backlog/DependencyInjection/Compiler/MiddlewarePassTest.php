<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\DependencyInjection\Compiler;

use Fedot\Backlog\DependencyInjection\Compiler\MiddlewarePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tests\Fedot\Backlog\BaseTestCase;

class MiddlewarePassTest extends BaseTestCase
{
    public function testServiceReplaceArguments()
    {
        $middlewares = [
            'm3' => ['tag' => []],
            'm1' => ['tag' => ['priority' => 200]],
            'm2' => ['tag' => ['priority' => 100]],
        ];

        $expectedMiddlewares = [
            new Reference('m1'),
            new Reference('m2'),
            new Reference('m3'),
        ];

        $definition = $this->createMock(Definition::class);
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('backlog.infrastructure.runner-factory')
            ->willReturn(true)
        ;
        $container->expects($this->once())
            ->method('getDefinition')
            ->with('backlog.infrastructure.runner-factory')
            ->willReturn($definition)
        ;
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('backlog.middleware')
            ->willReturn($middlewares)
        ;

        $definition->expects($this->once())
            ->method('setAutowired')
            ->with(false)
            ->willReturnSelf()
        ;
        $definition->expects($this->once())
            ->method('setArguments')
            ->with([$expectedMiddlewares])
            ->willReturnSelf()
        ;

        $middlewarePass = new MiddlewarePass();

        $middlewarePass->process($container);
    }

    public function testHasNotDefinition()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('backlog.infrastructure.runner-factory')
            ->willReturn(false)
        ;

        $container->expects($this->never())->method('getDefinition');
        $container->expects($this->never())->method('findTaggedServiceIds');

        $middlewarePass = new MiddlewarePass();

        $middlewarePass->process($container);
    }
}
