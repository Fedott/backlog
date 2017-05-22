<?php declare(strict_types=1);
namespace Fedot\Backlog\Action;

use Amp\Promise;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use function Amp\call;

abstract class AbstractAction implements ActionInterface
{
    abstract protected function execute(RequestInterface $request, ResponseInterface $response);

    public function process(RequestInterface $request, ResponseInterface $response): Promise
    {
        return call(\Closure::fromCallable([$this, 'execute']), $request, $response);
    }

    public function supportsRequest(RequestInterface $request): bool
    {
        return $request->getType() === $this->getSupportedType();
    }
}
