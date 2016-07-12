<?php
namespace Fedot\Backlog\Response;

use Aerys\Websocket\Endpoint;
use Fedot\Backlog\Response\Response;

class ResponseSender
{
    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @param Endpoint $endpoint
     */
    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param Response $response
     * @param int|null|array $clientId
     */
    public function sendResponse(Response $response, $clientId = null)
    {
        $this->endpoint->send($clientId, json_encode($response));
    }
}
