<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Middleware;

use Fedot\Backlog\Infrastructure\Middleware\Runner;
use Fedot\Backlog\Infrastructure\Middleware\RunnerFactory;
use Tests\Fedot\Backlog\BaseTestCase;

class RunnerFactoryTest extends BaseTestCase
{
    public function testNewInstance()
    {
        $middlewareQueue = [
            function() {},
            function() {},
            function() {},
        ];

        $runnerFactory = new \Fedot\Backlog\Infrastructure\Middleware\RunnerFactory($middlewareQueue);

        $runner = $runnerFactory->newInstance();

        $this->assertInstanceOf(\Fedot\Backlog\Infrastructure\Middleware\Runner::class, $runner);
    }
}
