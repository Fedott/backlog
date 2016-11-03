<?php declare(strict_types=1);

namespace Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocket\ResponseInterface;

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

    public function processMessage(Endpoint $endpoint, int $clientId, string $message): Promise
    {
        $request = $this->serializerService->parseRequest($message);
        $request = $request->withClientId($clientId);
        $response = new Response($request->getId(), $request->getClientId());

        try {
            $request = $request->withAttribute('payloadObject', $this->serializerService->parsePayload($request));

            $responsePromise = $this->requestProcessorManager->process($request, $response);
        } catch (\RuntimeException $exception) {
            $payload = new ErrorPayload();
            $payload->message = $exception->getMessage();
            $response = $response->withType('error');
            $response = $response->withPayload((array) $payload);

            $responsePromise = new Success($response);
        }

        $responsePromise->when(function ($error, ResponseInterface $response) use ($endpoint) {
            $responseBody = json_encode($response);
            if ($response->isDirect()) {
                $endpoint->send($response->getClientId(), $responseBody);
            } else {
                $endpoint->send(null, $responseBody);
            }
        });

        return $responsePromise;
    }
}
