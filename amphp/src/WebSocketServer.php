<?php
namespace Fedot\Backlog;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;

class WebSocketServer implements Websocket
{
    /**
     * @var Websocket\Endpoint
     */
    protected $endpoint;

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
        $msgBody = json_decode(yield $msg, true);
        $this->endpoint->send($clientId, json_encode([
            "id" => $msgBody['id'],
            "type" => "echo",
            "body" => json_encode($msgBody),
        ]));
    }

    /**
     * @inheritDoc
     */
    public function onClose(int $clientId, int $code, string $reason)
    {
        // TODO: Implement onClose() method.
    }

    /**
     * @inheritDoc
     */
    public function onStop()
    {
        // TODO: Implement onStop() method.
    }
}
