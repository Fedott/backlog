<?php

namespace Fedot\Backlog\WebSocket;

interface RequestInterface
{
    public function getId(): int;
    public function withId(int $id): RequestInterface;

    public function getClientId(): int;
    public function withClientId(int $clientId): RequestInterface;

    public function getType(): string;
    public function withType(string $type): RequestInterface;

    public function getPayload(): array;
    public function withPayload(array $payload): RequestInterface;

    public function getAttributes(): array;
    public function getAttribute(string $attribute, $default = null);
    public function withAttribute(string $attribute, $value): RequestInterface;
    public function withoutAttribute(string $attribute): RequestInterface;
}
