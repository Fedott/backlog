<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Infrastructure\Middleware;

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

        $runnerFactory = new RunnerFactory($middlewareQueue);

        $runner = $runnerFactory->newInstance();

        $this->assertInstanceOf(Runner::class, $runner);
    }
}
