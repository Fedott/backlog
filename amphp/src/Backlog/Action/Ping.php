<?php declare(strict_types=1);
namespace Fedot\Backlog\Action;

use Amp\Deferred as Promisor;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class Ping extends AbstractAction
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

        $promisor->resolve($response);
    }
}
