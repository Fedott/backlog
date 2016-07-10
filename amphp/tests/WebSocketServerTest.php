<?php
use Aerys\Websocket\Endpoint;
use Fedot\Backlog\Request;
use Fedot\Backlog\RequestProcessor;
use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocketServer;

class WebSocketServerTest extends PHPUnit_Framework_TestCase
{
    public function testProcessMessage()
    {
        $serializerServiceMock = $this->getMockBuilder(SerializerService::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $requestProcessorMock = $this->getMockBuilder(RequestProcessor::class)
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
        $this->assertEquals($endpointMock, $request->getEndpoint());
    }
}
