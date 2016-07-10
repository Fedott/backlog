<?php
namespace Fedot\Backlog;

use Aerys\Websocket\Endpoint;

class Request
{
    /**
     * @var int
     */
    protected $clientId;

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @return int
     */
    public function getId(): int
    {
    }

    /**
     * @return Payload
     */
    public function getPayload(): Payload
    {
    }

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @param int $clientId
     *
     * @return $this
     */
    public function setClientId(int $clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
    }

    /**
     * @param Endpoint $endpoint
     *
     * @return $this
     */
    public function setEndpoint(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
