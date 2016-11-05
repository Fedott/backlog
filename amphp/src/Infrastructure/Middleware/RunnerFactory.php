<?php declare(strict_types = 1);
namespace Fedot\Backlog\Infrastructure\Middleware;

class RunnerFactory
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

    public function newInstance()
    {
        return new Runner($this->queue);
    }
}
