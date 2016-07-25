<?php

namespace Fedot\Backlog;

use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;

class MessageProcessor
{
    /**
     * @var SerializerService
     */
    protected $serializerService;
    /**
     * @var RequestProcessorManager
     */
    protected $requestProcessorManager;

    /**
     * MessageProcessor constructor.
     *
     * @param SerializerService       $serializerService
     * @param RequestProcessorManager $requestProcessorManager
     */
    public function __construct(SerializerService $serializerService, RequestProcessorManager $requestProcessorManager)
    {
        $this->serializerService = $serializerService;
        $this->requestProcessorManager = $requestProcessorManager;
    }

    /**
     * @param int            $clientId
     * @param string         $message
     * @param ResponseSender $responseSender
     */
    public function processMessage(int $clientId, string $message, ResponseSender $responseSender)
    {
        $request = $this->serializerService->parseRequest($message);
        $request->setClientId($clientId);
        $request->setResponseSender($responseSender);

        try {
            $request->payload = $this->serializerService->parsePayload($request);

            $this->requestProcessorManager->process($request);
        } catch (\RuntimeException $exception) {
            $response = new Response();
            $response->requestId = $request->id;
            $response->type = 'error';
            $response->payload = new ErrorPayload();
            $response->payload->message = $exception->getMessage();

            $request->getResponseSender()->sendResponse($response, $request->getClientId());
        }
    }
}
