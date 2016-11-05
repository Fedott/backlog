<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Middleware;

use Fedot\Backlog\Infrastructure\Middleware\Runner;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class RunnerTest extends BaseTestCase
{
    public function testInvoke()
    {
        $runCount = 0;
        $middleware = function (RequestInterface $request, ResponseInterface $response, callable $next = null) use (
            &
            $runCount
        ) {
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

        $runner = new Runner($middlewareQueue);
        $request = new Request(1, 'test', 31);
        $response = new Response(1, 31);

        $actualResponse = $runner($request, $response);

        $this->assertEquals($response, $actualResponse);
        $this->assertEquals(3, $runCount);
    }
}
