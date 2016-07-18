<?php
namespace Tests\Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocketServer;

class WebSocketServerTest extends BaseTestCase
{
    public function testProcessMessage()
    {
        $serializerServiceMock = $this->createMock(SerializerService::class);
        $requestProcessorMock = $this->createMock(RequestProcessorManager::class);
        $endpointMock = $this->createMock(Endpoint::class);
        $webSocketServer = new WebSocketServer($serializerServiceMock, $requestProcessorMock);
        $webSocketServer->onStart($endpointMock);

        $request = new Request();

        $serializerServiceMock->expects($this->once())
            ->method('parseRequest')
            ->with("jj")
            ->willReturn($request)
        ;
        $requestProcessorMock->expects($this->once())
            ->method('process')
            ->with($request)
        ;

        $webSocketServer->processMessage(123, "jj");

        $this->assertEquals(123, $request->getClientId());

        $responseSender = $request->getResponseSender();
        $this->assertInstanceOf(ResponseSender::class, $responseSender);
    }

    public function testProcessMessageWithError()
    {
        $serializerServiceMock = $this->createMock(SerializerService::class);
        $requestProcessorMock = $this->createMock(RequestProcessorManager::class);
        $endpointMock = $this->createMock(Endpoint::class);
        $webSocketServer = new WebSocketServer($serializerServiceMock, $requestProcessorMock);
        $webSocketServer->onStart($endpointMock);

        $request = new Request();
        $request->id = 434;
        $request->type = 'test-error';
        $request->setClientId(675);

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
            ->with(
                $this->equalTo(123),
                $this->equalTo('{"requestId":434,"type":"error","payload":{"message":"Not found payload type: qwe"}}')
            )
        ;

        $webSocketServer->processMessage(123, "jj");
    }
}
