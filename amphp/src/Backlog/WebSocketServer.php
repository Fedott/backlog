<?php declare(strict_types=1);
namespace Fedot\Backlog;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;
use Generator;

class WebSocketServer implements Websocket
{
    /**
     * @var MessageProcessor
     */
    protected $messageProcessor;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    /**
     * @var Websocket\Endpoint
     */
    protected $endpoint;

    public function __construct(
        MessageProcessor $messageProcessor,
        WebSocketConnectionAuthenticationService $webSocketAuthService
    ) {
        $this->messageProcessor = $messageProcessor;
        $this->webSocketAuthService = $webSocketAuthService;
    }

    public function onStart(Websocket\Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function onHandshake(Request $request, Response $response)
    {
    }

    public function onOpen(int $clientId, $handshakeData)
    {
        $this->endpoint->send($clientId, json_encode([
            "id" => null,
            "type" => "hello",
        ]));
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function onData(int $clientId, Websocket\Message $msg)
    {
        yield from $this->processMessage($clientId, yield $msg);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param int $clientId
     * @param string $message
     *
     * @return Generator
     */
    public function processMessage(int $clientId, string $message): Generator
    {
        yield from $this->messageProcessor->processMessage($this->endpoint, $clientId, $message);
    }

    public function onClose(int $clientId, int $code, string $reason)
    {
        $this->webSocketAuthService->unauthorizeClient($clientId);
    }

    public function onStop()
    {
    }
}
