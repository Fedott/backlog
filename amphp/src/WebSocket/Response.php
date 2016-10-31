<?php

namespace Fedot\Backlog\WebSocket;

class Response implements ResponseInterface
{
    /**
     * @var int
     */
    private $requestId;

    /**
     * @var int
     */
    private $clientId;

    /**
     * @var bool
     */
    private $isDirect;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $payload;

    public function __construct(int $requestId, int $clientId, string $type = null, array $payload = [], $isDirect = true)
    {
        $this->requestId = $requestId;
        $this->clientId = $clientId;
        $this->isDirect = $isDirect;
        $this->type = $type;
        $this->payload = $payload;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function withRequestId(int $requestId): ResponseInterface
    {
        $new = clone $this;
        $new->requestId = $requestId;

        return $new;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function withClientId(int $clientId): ResponseInterface
    {
        $new = clone $this;
        $new->clientId = $clientId;

        return $new;
    }

    public function isDirect(): bool
    {
        return $this->isDirect;
    }

    public function withIsDirect(bool $isDirect): ResponseInterface
    {
        $new = clone $this;
        $new->isDirect = $isDirect;

        return $new;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function withType(string $type)
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function withPayload(array $payload): ResponseInterface
    {
        $new = clone $this;
        $new->payload = $payload;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'requestId' => $this->requestId,
            'type' => $this->type,
            'payload' => $this->payload,
        ];
    }
}
