<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Deferred;
use Amp\Promise;
use Amp\Promisor;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

abstract class AbstractProcessor implements ProcessorInterface
{
    abstract protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response);

    /**
     * @inheritdoc
     */
    public function process(RequestInterface $request, ResponseInterface $response): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $request, $response) {
            yield from $this->execute($promisor, $request, $response);
        });

        return $promisor->promise();
    }

    public function supportsRequest(RequestInterface $request): bool
    {
        return $request->getType() === $this->getSupportedType();
    }
}
