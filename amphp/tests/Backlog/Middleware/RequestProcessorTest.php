<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Middleware;

use Amp\Success;
use Fedot\Backlog\ActionManager;
use Fedot\Backlog\Middleware\ActionProcessor;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class RequestProcessorTest extends BaseTestCase
{
    public function testWithoutNext()
    {
        $requestProcessorManagerMock = $this->createMock(ActionManager::class);
        $requestProcessor = new ActionProcessor($requestProcessorManagerMock);

        $request = new Request(1, 'test', 3);
        $incomingResponse = new Response($request->getId(), $request->getClientId());
        $processedResponse = $incomingResponse->withPayload(['test' => 'test']);

        $requestProcessorManagerMock->expects($this->once())
            ->method('process')
            ->with($request, $incomingResponse)
            ->willReturn(new Success($processedResponse))
        ;

        $actualResponse = \Amp\Promise\wait($requestProcessor($request, $incomingResponse));

        $this->assertNotEquals($incomingResponse, $actualResponse);
        $this->assertEquals($processedResponse, $actualResponse);
    }

    public function testWithNext()
    {
        $requestProcessorManagerMock = $this->createMock(ActionManager::class);
        $requestProcessor = new ActionProcessor($requestProcessorManagerMock);

        $request = new Request(1, 'test', 3);
        $incomingResponse = new Response($request->getId(), $request->getClientId());
        $processedResponse = $incomingResponse->withPayload(['test' => 'test']);
        $nextResponse = $processedResponse->withType('next-type');

        $requestProcessorManagerMock->expects($this->once())
            ->method('process')
            ->with($request, $incomingResponse)
            ->willReturn(new Success($processedResponse))
        ;

        $next = function (RequestInterface $prevRequest, ResponseInterface $prevResponse) use ($request, $incomingResponse, $processedResponse, $nextResponse) {
            $this->assertNotEquals($prevResponse, $incomingResponse);
            $this->assertEquals($prevResponse, $processedResponse);
            $this->assertEquals($request, $prevRequest);

            return new Success($nextResponse);
        };

        $actualResponse = \Amp\Promise\wait($requestProcessor($request, $incomingResponse, $next));

        $this->assertNotEquals($incomingResponse, $actualResponse);
        $this->assertNotEquals($processedResponse, $actualResponse);
        $this->assertEquals($nextResponse, $actualResponse);
    }
}
