<?php declare(strict_types = 1);
namespace Fedot\Backlog\Infrastructure\Middleware;

use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class Runner
{
    /**
     * @var callable|MiddlewareInterface[]
     */
    protected $queue = [];

    /**
     * @param callable|MiddlewareInterface[] $queue
     */
    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $middleware = array_shift($this->queue);

        if (null === $middleware) {
            return $response;
        }

        return $middleware($request, $response, $this);
    }
}
