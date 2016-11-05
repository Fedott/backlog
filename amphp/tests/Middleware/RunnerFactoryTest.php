<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Middleware;

use Fedot\Backlog\Middleware\Runner;
use Fedot\Backlog\Middleware\RunnerFactory;
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
