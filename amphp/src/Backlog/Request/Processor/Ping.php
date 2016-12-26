<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\PongPayload;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class Ping extends AbstractProcessor
{
    public function getSupportedType(): string
    {
        return 'ping';
    }

    public function getExpectedRequestPayload(): string
    {
        return EmptyPayload::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        $response = $response->withType('pong');
        $response = $response->withPayload((array) (new PongPayload()));

        $promisor->succeed($response);
    }
}
