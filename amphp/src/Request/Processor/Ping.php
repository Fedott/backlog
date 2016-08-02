<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\PongPayload;
use Fedot\Backlog\Response\Response;

class Ping implements ProcessorInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool
    {
        return $request->type === $this->getSupportedType();
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
     * @param Request $request
     *
     * @return \Generator
     */
    public function process(Request $request)
    {
        $response = new Response();
        $response->requestId = $request->id;
        $response->type = 'pong';
        $response->payload = new PongPayload();

        $request->getResponseSender()->sendResponse($response, $request->getClientId());

        yield;
    }
}
