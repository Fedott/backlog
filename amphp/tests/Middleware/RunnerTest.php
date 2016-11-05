<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Middleware;

use Fedot\Backlog\Infrastructure\Middleware\Runner;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\BaseTestCase;

class RunnerTest extends BaseTestCase
{
    public function testInvoke()
    {
        $runCount = 0;
        $middleware = function (Request $request, Response $response, callable $next = null) use (&$runCount) {
            $runCount++;

            if (null !== $next) {
                $response = $next($request, $response, $next);
            }

            return $response;
        };

        $middlewareQueue = [
            $middleware,
            $middleware,
            $middleware
        ];

        $runner = new \Fedot\Backlog\Infrastructure\Middleware\Runner($middlewareQueue);
        $request = new Request(1, 'test', 31);
        $response = new Response(1, 31);

        $actualResponse = $runner($request, $response);

        $this->assertEquals($response, $actualResponse);
        $this->assertEquals(3, $runCount);
    }
}
