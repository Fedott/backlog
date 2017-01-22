<?php declare(strict_types=1);
namespace Fedot\Backlog\Action;

use AsyncInterop\Promise;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

interface ActionInterface
{
    public function supportsRequest(RequestInterface $request): bool;

    public function getSupportedType(): string;

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string;

    public function process(RequestInterface $request, ResponseInterface $response): Promise;
}
