<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Amp\Success;
use Fedot\Backlog\MessageProcessor;
use Fedot\Backlog\Payload\ErrorPayload;
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

        $webSocketServer = new MessageProcessor($serializerServiceMock, $requestProcessorMock);

        $request = new Request(1, 123, 'test');

        $serializerServiceMock->expects($this->once())
            ->method('parseRequest')
            ->with("jj")
            ->willReturn($request)
        ;
        $requestProcessorMock->expects($this->once())
            ->method('process')
            ->with($request)
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

        $request = new Request(434, 675, 'test-error');

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
            ->with(675, '{"requestId":434,"type":"error","payload":[]}')
        ;

        \Amp\wait($webSocketServer->processMessage($endpointMock, 675, "jj"));
    }
}
