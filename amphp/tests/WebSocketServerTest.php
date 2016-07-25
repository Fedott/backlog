<?php
namespace Tests\Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Fedot\Backlog\MessageProcessor;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocketServer;

class WebSocketServerTest extends BaseTestCase
{
    public function testProcessMessage()
    {
        $messageProcessorMock = $this->createMock(MessageProcessor::class);
        $endpointMock = $this->createMock(Endpoint::class);
        $webSocketServer = new WebSocketServer($messageProcessorMock);
        $webSocketServer->onStart($endpointMock);

        $messageProcessorMock->expects($this->once())
            ->method('processMessage')
            ->with(
                $this->equalTo(123),
                $this->equalTo('jj'),
                $this->isInstanceOf(ResponseSender::class)
            )
        ;

        $webSocketServer->processMessage(123, "jj");
    }
}
