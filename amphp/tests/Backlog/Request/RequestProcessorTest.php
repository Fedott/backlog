<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request;

use Amp\Success;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\BaseTestCase;

class RequestProcessorTest extends BaseTestCase
{
    public function testProcess()
    {
        $request = new Request(1, 'test', 1);
        $response = new Response(1, 1);
        $expectedResponse = $response->withType('expected');

        $testProcessor1 = $this->createMock(ProcessorInterface::class);
        $testProcessor2 = $this->createMock(ProcessorInterface::class);
        $testProcessor3 = $this->createMock(ProcessorInterface::class);

        $testProcessor1->expects($this->once())
            ->method('supportsRequest')
            ->with($request)
            ->willReturn(false)
        ;
        $testProcessor1->expects($this->never())
            ->method('process')
            ->with($request)
        ;
        $testProcessor2->expects($this->once())
            ->method('supportsRequest')
            ->with($request)
            ->willReturn(true)
        ;
        $testProcessor2->expects($this->once())
            ->method('process')
            ->with($request)
            ->willReturn(new Success($expectedResponse))
        ;
        $testProcessor3->expects($this->never())
            ->method('supportsRequest')
            ->with($request)
        ;
        $testProcessor3->expects($this->never())
            ->method('process')
            ->with($request)
        ;

        $manager = new RequestProcessorManager();
        $manager->addProcessors([
            $testProcessor1,
            $testProcessor2,
            $testProcessor3,
        ]);

        $actualResponse = \Amp\wait($manager->process($request, $response));

        $this->assertEquals($expectedResponse, $actualResponse);
    }
    public function testProcessWithoutProcessors()
    {
        $request = new Request(1, 'test', 1);
        $response = new Response(1, 1);

        $manager = new RequestProcessorManager();

        $actualResponse = \Amp\wait($manager->process($request, $response));

        $this->assertEquals($response, $actualResponse);
    }
}
