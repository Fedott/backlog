<?php declare(strict_types = 1);
namespace Fedot\Backlog\Middleware;

use Amp\Deferred;
use Amp\Promise;
use Fedot\Backlog\Infrastructure\Middleware\MiddlewareInterface;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class RequestProcessor implements MiddlewareInterface
{
    /**
     * @var RequestProcessorManager
     */
    private $requestProcessorManager;

    public function __construct(RequestProcessorManager $requestProcessorManager)
    {
        $this->requestProcessorManager = $requestProcessorManager;
    }

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ): Promise {
        $responsePromise = $this->requestProcessorManager->process($request, $response);

        if (null === $next) {
            return $responsePromise;
        }

        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $request, $responsePromise, $next) {
            $response = yield $responsePromise;

            $nextResponse = yield $next($request, $response);

            $promisor->succeed($nextResponse);
        });

        return $promisor->promise();
    }
}
