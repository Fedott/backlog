<?php
declare(strict_types=1);

namespace Fedot\Backlog\WebSocket;

use JsonSerializable;

interface ResponseInterface extends JsonSerializable
{
    public function getRequestId(): int;
    public function withRequestId(int $requestId): ResponseInterface;

    public function getClientId(): int;
    public function withClientId(int $clientId): ResponseInterface;

    public function isDirect(): bool;
    public function withIsDirect(bool $isDirect): ResponseInterface;

    public function getType(): string;
    public function withType(string $type): ResponseInterface;

    public function getPayload(): array;
    public function withPayload(array $payload): ResponseInterface;
}
