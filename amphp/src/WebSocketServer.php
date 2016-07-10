<?php
namespace Fedot\Backlog;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;

class WebSocketServer implements Websocket
{
    /**
     * @var SerializerService
     */
    protected $serializerService;

    /**
     * @var RequestProcessor
     */
    protected $requestProcessor;

    /**
     * @var Websocket\Endpoint
     */
    protected $endpoint;

    /**
     * WebSocketServer constructor.
     *
     * @param SerializerService $serializerService
     * @param RequestProcessor  $requestProcessor
     */
    public function __construct(SerializerService $serializerService, RequestProcessor $requestProcessor)
    {
        $this->serializerService = $serializerService;
        $this->requestProcessor = $requestProcessor;
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
        $request = $this->serializerService->parseRequest($message);
        $request->setClientId($clientId);
        $request->setEndpoint($this->endpoint);

        $this->requestProcessor->process($request);
    }

    /**
     * @inheritDoc
     */
    public function onClose(int $clientId, int $code, string $reason)
    {
    }

    /**
     * @inheritDoc
     */
    public function onStop()
    {
    }
}
