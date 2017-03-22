<?php declare(strict_types=1);
namespace Fedot\Backlog\Action;

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

abstract class AbstractAction implements ActionInterface
{
    abstract protected function execute(Deferred $promisor, RequestInterface $request, ResponseInterface $response);

    public function process(RequestInterface $request, ResponseInterface $response): Promise
    {
        $promisor = new Deferred();

        Loop::defer(function () use ($promisor, $request, $response) {
            $generator = $this->execute($promisor, $request, $response);

            if (null !== $generator) {
                yield from $generator;
            }
        });

        return $promisor->promise();
    }

    public function supportsRequest(RequestInterface $request): bool
    {
        return $request->getType() === $this->getSupportedType();
    }
}
