<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Payload\PongPayload;
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
        return $request->type === 'ping';
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function process(Request $request): bool
    {
        $response = new Response();
        $response->requestId = $request->id;
        $response->type = 'pong';
        $response->payload = new PongPayload();

        $request->getResponseSender()->sendResponse($response, $request->getClientId());

        return true;
    }
}
