<?php declare(strict_types=1);
namespace Fedot\Backlog\Action;

use AsyncInterop\Promise;
use Amp\Success;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class ActionManager
{
    /**
     * @var ActionInterface[]
     */
    protected $actions = [];

    public function registerAction(ActionInterface $action)
    {
        $this->actions[] = $action;
    }

    /**
     * @param ActionInterface[] $actions
     */
    public function registerActions(array $actions)
    {
        array_map(function (ActionInterface $action) {
            $this->registerAction($action);
        }, $actions);
    }

    public function process(RequestInterface $request, ResponseInterface $response): Promise
    {
        foreach ($this->actions as $action) {
            if ($action->supportsRequest($request)) {
                return $action->process($request, $response);
            }
        }

        return new Success($response);
    }
}
