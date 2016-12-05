<?php declare(strict_types=1);
namespace Fedot\Backlog;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;

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

    /**
     * WebSocketServer constructor.
     *
     * @param MessageProcessor                         $messageProcessor
     * @param WebSocketConnectionAuthenticationService $webSocketAuthService
     */
    public function __construct(
        MessageProcessor $messageProcessor,
        WebSocketConnectionAuthenticationService $webSocketAuthService
    ) {
        $this->messageProcessor = $messageProcessor;
        $this->webSocketAuthService = $webSocketAuthService;
    }

    /**
     * @inheritDoc
     */
    public function onStart(Websocket\Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @inheritDoc
     */
    public function onHandshake(Request $request, Response $response)
    {
    }

    /**
     * @inheritDoc
     */
    public function onOpen(int $clientId, $handshakeData)
    {
        $this->endpoint->send($clientId, json_encode([
            "id" => null,
            "type" => "hello",
        ]));
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnoreStart
     */
    public function onData(int $clientId, Websocket\Message $msg)
    {
        yield from $this->processMessage($clientId, yield $msg);
    }
    // @codeCoverageIgnoreEnd

    public function processMessage(int $clientId, string $message)
    {
        yield from $this->messageProcessor->processMessage($this->endpoint, $clientId, $message);
    }

    /**
     * @inheritDoc
     */
    public function onClose(int $clientId, int $code, string $reason)
    {
        $this->webSocketAuthService->unauthorizeClient($clientId);
    }

    /**
     * @inheritDoc
     */
    public function onStop()
    {
    }
}
