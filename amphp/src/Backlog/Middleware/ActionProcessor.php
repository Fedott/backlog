<?php declare(strict_types=1);
namespace Fedot\Backlog\Middleware;

use function Amp\call;
use Amp\Promise;
use Fedot\Backlog\ActionManager;
use Fedot\Backlog\Infrastructure\Middleware\MiddlewareInterface;
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

        return call(function (RequestInterface $request, Promise $responsePromise, callable $next) {
            return yield $next($request, yield $responsePromise);
        }, $request, $responsePromise, $next);
    }
}
