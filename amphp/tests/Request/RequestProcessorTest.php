<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request;

use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Request\RequestProcessorManager;
use Tests\Fedot\Backlog\BaseTestCase;

class RequestProcessorTest extends BaseTestCase
{
    public function testProcess()
    {
        $request = new Request();

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
            ->willReturnCallback(function() {
                yield;
            })
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

        $manager->process($request);

        $this->waitAsyncCode();
    }
}
