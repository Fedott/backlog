<?php declare(strict_types = 1);
namespace Fedot\Backlog\Infrastructure\Middleware;

use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;

class Runner
{
    /**
     * @var callable[]
     */
    protected $queue = [];

    /**
     * @param callable[] $queue
     */
    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $middleware = array_shift($this->queue);

        if (null === $middleware) {
            return $response;
        }

        return $middleware($request, $response, $this);
    }
}
