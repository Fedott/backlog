<?php
namespace Fedot\Backlog\Request;

use Aerys\Websocket\Endpoint;
use Fedot\Backlog\PayloadInterface;

class Request
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var PayloadInterface
     */
    public $payload;

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
