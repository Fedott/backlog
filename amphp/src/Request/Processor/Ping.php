<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\PongPayload;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;

class Ping implements ProcessorInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool
    {
        return $request->getType() === $this->getSupportedType();
    }

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

    /**
     * @inheritdoc
     */
    public function process(Request $request, Response $response): Promise
    {
        $response = $response->withType('pong');
        $response = $response->withPayload((array) (new PongPayload()));

        return new Success($response);
    }
}
