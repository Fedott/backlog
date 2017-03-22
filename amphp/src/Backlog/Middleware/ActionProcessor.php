<?php declare(strict_types=1);
namespace Fedot\Backlog\Middleware;

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;
use Fedot\Backlog\Action\ActionManager;
use Fedot\Backlog\Infrastructure\Middleware\MiddlewareInterface;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use function Amp\wrap;

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

        Loop::defer(wrap(function () use ($promisor, $request, $responsePromise, $next) {
            $response = yield $responsePromise;

            $nextResponse = yield $next($request, $response);

            $promisor->resolve($nextResponse);
        }));

        return $promisor->promise();
    }
}
