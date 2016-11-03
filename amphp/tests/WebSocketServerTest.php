<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Fedot\Backlog\MessageProcessor;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Fedot\Backlog\WebSocketServer;

class WebSocketServerTest extends BaseTestCase
{
    public function testProcessMessage()
    {
        $messageProcessorMock = $this->createMock(MessageProcessor::class);
        $endpointMock = $this->createMock(Endpoint::class);
        $webSocketServer = new WebSocketServer(
            $messageProcessorMock,
            $this->createMock(WebSocketConnectionAuthenticationService::class)
        );
        $webSocketServer->onStart($endpointMock);

        $messageProcessorMock->expects($this->once())
            ->method('processMessage')
            ->with(
                $endpointMock,
                123,
                'jj'
            )
        ;

        $webSocketServer->processMessage(123, "jj");
    }

    public function testOnClose()
    {
        $webSocketAuthService = $this->createMock(WebSocketConnectionAuthenticationService::class);
        $webSocketServer = new WebSocketServer(
            $this->createMock(MessageProcessor::class),
            $webSocketAuthService
        );

        $webSocketAuthService->expects($this->once())
            ->method('unauthorizeClient')
            ->with($this->equalTo(77))
        ;

        $webSocketServer->onClose(77, 200, 'reason');
    }
}
