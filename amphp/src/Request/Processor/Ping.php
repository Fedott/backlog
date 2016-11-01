<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Promisor;
use Amp\Success;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\PongPayload;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;

class Ping extends AbstractProcessor
{
    /**
     * @inheritDoc
     */
    public function getSupportedType(): string
    {
        return 'ping';
    }

    /**
     * @inheritDoc
     */
    public function getExpectedRequestPayload(): string
    {
        return EmptyPayload::class;
    }

    protected function execute(Promisor $promisor, Request $request, Response $response)
    {
        $response = $response->withType('pong');
        $response = $response->withPayload((array) (new PongPayload()));

        $promisor->succeed($response);
    }
}
