<?php declare(strict_types = 1);
namespace Fedot\Backlog\Infrastructure\Middleware;

use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

interface MiddlewareInterface
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ): ResponseInterface;
}
