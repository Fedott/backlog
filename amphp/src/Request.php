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
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var PayloadInterface
     */
    protected $payload;

    /**
     * Request constructor.
     *
     * @param int              $id
     * @param string           $type
     * @param PayloadInterface $payload
     */
    public function __construct(int $id, string $type, PayloadInterface $payload)
    {
        $this->id      = $id;
        $this->type    = $type;
        $this->payload = $payload;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
