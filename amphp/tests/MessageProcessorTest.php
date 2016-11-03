<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Amp\Success;
use Fedot\Backlog\MessageProcessor;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\PayloadInterface;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;

class MessageProcessorTest extends BaseTestCase
{
    public function testProcessMessage()
    {
        $serializerServiceMock = $this->createMock(SerializerService::class);
        $requestProcessorMock = $this->createMock(RequestProcessorManager::class);
        $endpointMock = $this->createMock(Endpoint::class);
        $payloadMock = $this->createMock(PayloadInterface::class);

        $webSocketServer = new MessageProcessor($serializerServiceMock, $requestProcessorMock);

        $request = new Request(1, 'test', 123, ['test' => 'test']);

        $serializerServiceMock->expects($this->once())
            ->method('parseRequest')
            ->with("jj")
            ->willReturn($request)
        ;
        $serializerServiceMock->expects($this->once())
            ->method('parsePayload')
            ->with($request)
            ->willReturn($payloadMock)
        ;
        $requestProcessorMock->expects($this->once())
            ->method('process')
            ->with($this->callback(function (Request $request) use ($payloadMock) {
                $this->assertEquals(1, $request->getId());
                $this->assertEquals(123, $request->getClientId());
                $this->assertEquals('test', $request->getType());
                $this->assertEquals($payloadMock, $request->getAttribute('payloadObject'));

                return true;
            }))
            ->willReturn(new Success(new Response($request->getId(), $request->getClientId(), 'test-response')))
        ;

        $endpointMock->expects($this->once())
            ->method('send')
            ->with(123, '{"requestId":1,"type":"test-response","payload":[]}')
        ;

        \Amp\wait($webSocketServer->processMessage($endpointMock, 123, "jj"));
    }

    public function testProcessMessageWithError()
    {
        $serializerServiceMock = $this->createMock(SerializerService::class);
        $requestProcessorMock = $this->createMock(RequestProcessorManager::class);
        $endpointMock = $this->createMock(Endpoint::class);

        $webSocketServer = new MessageProcessor($serializerServiceMock, $requestProcessorMock);

        $request = new Request(434, 'test-error', 675);

        $serializerServiceMock->expects($this->once())
            ->method('parseRequest')
            ->with("jj")
            ->willReturn($request)
        ;
        $serializerServiceMock->expects($this->once())
            ->method('parsePayload')
            ->with($request)
            ->willThrowException(new \RuntimeException("Not found payload type: qwe"))
        ;
        $requestProcessorMock->expects($this->never())
            ->method('process')
        ;

        $endpointMock->expects($this->once())
            ->method('send')
            ->with(675, '{"requestId":434,"type":"error","payload":{"message":"Not found payload type: qwe"}}')
        ;

        \Amp\wait($webSocketServer->processMessage($endpointMock, 675, "jj"));
    }
}
