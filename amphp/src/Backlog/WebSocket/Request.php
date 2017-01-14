<?php
declare(strict_types=1);

namespace Fedot\Backlog\WebSocket;

class Request implements RequestInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $clientId;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $payload;

    /**
     * @var array
     */
    private $attributes = [];

    public function __construct(int $id, string $type, int $clientId = null, $payload = [])
    {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->type = $type;
        $this->payload = (array) $payload;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function withId(int $id): RequestInterface
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function withClientId(int $clientId): RequestInterface
    {
        $new = clone $this;
        $new->clientId = $clientId;

        return $new;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function withType(string $type): RequestInterface
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function withPayload(array $payload): RequestInterface
    {
        $new = clone $this;
        $new->payload = $payload;

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $attribute, $default = null)
    {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    public function withAttribute(string $attribute, $value): RequestInterface
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    public function withoutAttribute(string $attribute): RequestInterface
    {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$attribute]);

        return $new;
    }
}
