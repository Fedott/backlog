<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Infrastructure\Middleware;

use Amp\Success;
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
                return $next($request, $response);
            }

            return new Success($response);
        };

        $middlewareQueue = [
            $middleware,
            $middleware,
            $middleware
        ];

        $runner = new Runner($middlewareQueue);
        $request = new Request(1, 'test', 31);
        $response = new Response(1, 31);

        $responsePromise = $runner($request, $response);
        $actualResponse = \Amp\wait($responsePromise);

        $this->assertEquals($response, $actualResponse);
        $this->assertEquals(3, $runCount);
    }
}
