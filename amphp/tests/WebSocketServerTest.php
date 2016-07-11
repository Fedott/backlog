<?php
namespace Tests\Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocketServer;
use PHPUnit_Framework_TestCase;

class WebSocketServerTest extends PHPUnit_Framework_TestCase
{
    public function testProcessMessage()
    {
        $serializerServiceMock = $this->getMockBuilder(SerializerService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $requestProcessorMock = $this->getMockBuilder(RequestProcessorManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $endpointMock = $this->getMockBuilder(Endpoint::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
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
