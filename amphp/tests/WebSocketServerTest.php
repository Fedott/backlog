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
}
