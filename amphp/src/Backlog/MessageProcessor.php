<?php declare(strict_types=1);

namespace Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Amp\Success;
use Fedot\Backlog\Infrastructure\Middleware\RunnerFactory;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
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
                $endpoint->send($responseBody, $response->getClientId());
            } else {
                $endpoint->broadcast($responseBody);
            }
        } catch (\Exception $exception) {
            $response = $response->withType('internal-server-error');
            $response = $response->withPayload(['message' => $exception->getMessage()]);

            $endpoint->send(json_encode($response), $clientId);
        }

        yield new Success();
    }
}
