<?php declare(strict_types=1);
namespace Fedot\Backlog\Middleware;

use Amp\Success;
use AsyncInterop\Promise;
use Fedot\Backlog\Infrastructure\Middleware\MiddlewareInterface;
use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class PayloadParser implements MiddlewareInterface
{
    /**
     * @var SerializerService
     */
    private $serializerService;

    public function __construct(SerializerService $serializerService)
    {
        $this->serializerService = $serializerService;
    }

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ): Promise {
        if (null === $next) {
            return new Success($response);
        }

        $payload = $this->serializerService->parsePayload($request);
        $request = $request->withAttribute('payloadObject', $payload);

        return $next($request, $response);
    }
}
