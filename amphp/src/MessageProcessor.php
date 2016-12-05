<?php declare(strict_types=1);

namespace Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Amp\Failure;
use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Infrastructure\Middleware\RunnerFactory;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Generator;
use Symfony\Component\Serializer\SerializerInterface;

class MessageProcessor
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var RunnerFactory
     */
    protected $middlewareRunnerFactory;

    public function __construct(
        RunnerFactory $middlewareRunnerFactory,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->middlewareRunnerFactory = $middlewareRunnerFactory;
    }

    public function processMessage(Endpoint $endpoint, int $clientId, string $message): Generator
    {
        /** @var Request $request */
        $request = $this->serializer->deserialize($message, Request::class, 'json');
        $request = $request->withClientId($clientId);
        $response = new Response($request->getId(), $request->getClientId());

        $runner = $this->middlewareRunnerFactory->newInstance();

        try {
            /** @var Response $response */
            $response = yield $runner($request, $response);

            $responseBody = json_encode($response);
            if ($response->isDirect()) {
                $endpoint->send($response->getClientId(), $responseBody);
            } else {
                $endpoint->send(null, $responseBody);
            }
        } catch (\Exception $exception) {
            $response = $response->withType('internal-server-error');

            $endpoint->send($clientId, json_encode($response));
        }

        yield;
    }
}
