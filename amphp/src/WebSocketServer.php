<?php declare(strict_types=1);
namespace Fedot\Backlog;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Response\Response as BacklogResponse;
use Fedot\Backlog\Response\ResponseSender;

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
     * @var ResponseSender
     */
    protected $responseSender;

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
        $this->responseSender = new ResponseSender($endpoint);
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
     */
    public function onData(int $clientId, Websocket\Message $msg)
    {
        $this->processMessage($clientId, yield $msg);
    }

    /**
     * @param int    $clientId
     * @param string $message
     */
    public function processMessage(int $clientId, string $message)
    {
        $this->messageProcessor->processMessage($clientId, $message, $this->responseSender);
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
