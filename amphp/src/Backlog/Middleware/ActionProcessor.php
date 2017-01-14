<?php declare(strict_types = 1);
namespace Fedot\Backlog\Middleware;

use Amp\Deferred;
use Amp\Promise;
use Fedot\Backlog\Infrastructure\Middleware\MiddlewareInterface;
use Fedot\Backlog\Action\ActionManager;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class ActionProcessor implements MiddlewareInterface
{
    /**
     * @var ActionManager
     */
    private $actionManager;

    public function __construct(ActionManager $actionManager)
    {
        $this->actionManager = $actionManager;
    }

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ): Promise {
        $responsePromise = $this->actionManager->process($request, $response);

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
