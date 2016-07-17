<?php
namespace Fedot\Backlog;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\ResponseSender;

class WebSocketServer implements Websocket
{
    /**
     * @var SerializerService
     */
    protected $serializerService;

    /**
     * @var RequestProcessorManager
     */
    protected $requestProcessorManager;

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
     * @param SerializerService       $serializerService
     * @param RequestProcessorManager $requestProcessorManager
     */
    public function __construct(SerializerService $serializerService, RequestProcessorManager $requestProcessorManager)
    {
        $this->serializerService       = $serializerService;
        $this->requestProcessorManager = $requestProcessorManager;
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
        $request = $this->serializerService->parseRequest($message);
        $request->setClientId($clientId);
        $request->setResponseSender($this->responseSender);

        $this->requestProcessorManager->process($request);
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