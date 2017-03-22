<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket\Endpoint;
use Amp\Loop;
use Amp\Success;
use Fedot\Backlog\MessageProcessor;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Fedot\Backlog\WebSocketServer;

class WebSocketServerTest extends BaseTestCase
{
    public static function setUpBeforeClass()
    {
        Loop::set(new Loop\NativeDriver());
    }

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
            ->willReturnCallback(function() {
                yield new Success();
            })
        ;

        Loop::run(function () use ($webSocketServer) {
            yield from $webSocketServer->processMessage(123, "jj");
        });

        $this->assertTrue(true);
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

    public function testOnHandshake()
    {
        $webSocketServer = new WebSocketServer(
            $this->createMock(MessageProcessor::class),
            $this->createMock(WebSocketConnectionAuthenticationService::class)
        );

        $webSocketServer->onHandshake(
            $this->createMock(Request::class),
            $this->createMock(Response::class)
        );

        $this->assertTrue(true);
    }

    public function testOnStop()
    {
        $webSocketServer = new WebSocketServer(
            $this->createMock(MessageProcessor::class),
            $this->createMock(WebSocketConnectionAuthenticationService::class)
        );

        $webSocketServer->onStop();

        $this->assertTrue(true);
    }

    public function testOnOpen()
    {
        $messageProcessorMock = $this->createMock(MessageProcessor::class);
        $endpointMock = $this->createMock(Endpoint::class);
        $webSocketServer = new WebSocketServer(
            $messageProcessorMock,
            $this->createMock(WebSocketConnectionAuthenticationService::class)
        );
        $webSocketServer->onStart($endpointMock);

        $endpointMock->expects($this->once())
            ->method('send')
        ;

        $webSocketServer->onOpen(22, '');
    }
}
