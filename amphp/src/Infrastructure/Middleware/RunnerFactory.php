<?php declare(strict_types = 1);
namespace Fedot\Backlog\Infrastructure\Middleware;

class RunnerFactory
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

    public function newInstance(): Runner
    {
        return new Runner($this->queue);
    }
}
