<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Deferred;
use Amp\Promisor;
use Amp\Promise;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;

abstract class AbstractProcessor implements ProcessorInterface
{
    abstract protected function execute(Promisor $promisor, Request $request, Response $response);

    /**
     * @inheritdoc
     */
    public function process(Request $request, Response $response): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $request, $response) {
            yield from $this->execute($promisor, $request, $response);
        });

        return $promisor->promise();
    }

    /**
     * @inheritdoc
     */
    public function supportsRequest(Request $request): bool
    {
        return $request->getType() === $this->getSupportedType();
    }
}
