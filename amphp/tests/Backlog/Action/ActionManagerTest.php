<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Action;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\ActionManager;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\BaseTestCase;

class ActionManagerTest extends BaseTestCase
{
    public function testProcess()
    {
        $request = new Request(1, 'test', 1);
        $response = new Response(1, 1);
        $expectedResponse = $response->withType('expected');

        $testAction1 = $this->createMock(ActionInterface::class);
        $testAction2 = $this->createMock(ActionInterface::class);
        $testAction3 = $this->createMock(ActionInterface::class);

        $testAction1->expects($this->once())
            ->method('supportsRequest')
            ->with($request)
            ->willReturn(false)
        ;
        $testAction1->expects($this->never())
            ->method('process')
            ->with($request)
        ;
        $testAction2->expects($this->once())
            ->method('supportsRequest')
            ->with($request)
            ->willReturn(true)
        ;
        $testAction2->expects($this->once())
            ->method('process')
            ->with($request)
            ->willReturn(new Success($expectedResponse))
        ;
        $testAction3->expects($this->never())
            ->method('supportsRequest')
            ->with($request)
        ;
        $testAction3->expects($this->never())
            ->method('process')
            ->with($request)
        ;

        $manager = new ActionManager();
        $manager->registerActions([
            $testAction1,
            $testAction2,
            $testAction3,
        ]);

        $actualResponse = \Amp\Promise\wait($manager->process($request, $response));

        $this->assertEquals($expectedResponse, $actualResponse);
    }
    public function testProcessWithoutActions()
    {
        $request = new Request(1, 'test', 1);
        $response = new Response(1, 1);

        $manager = new ActionManager();

        $actualResponse = \Amp\Promise\wait($manager->process($request, $response));

        $this->assertEquals($response, $actualResponse);
    }
}
