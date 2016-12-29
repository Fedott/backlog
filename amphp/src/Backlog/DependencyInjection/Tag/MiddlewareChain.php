<?php declare(strict_types = 1);
namespace Fedot\Backlog\DependencyInjection\Tag;

use Fedot\Backlog\Infrastructure\Middleware\MiddlewareInterface;

class MiddlewareChain
{
    private $middlewares;

    public function __construct()
    {
        $this->middlewares = [];
    }

    public function addMiddleware(MiddlewareInterface $middleware, int $priority)
    {
        $this->middlewares[$priority][] = $middleware;
    }

    public function getMiddlewares()
    {
        $middlewares = [];

        foreach ($this->middlewares as $priorityMiddleware) {
            foreach ($priorityMiddleware as $middleware) {
                $middlewares[] = $middleware;
            }
        }

        return $middlewares;
    }
}
