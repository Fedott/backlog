<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Stubs;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocket\ResponseInterface;

class TestProcessor implements ProcessorInterface
{
    /**
     * @param Request|RequestInterface $request
     *
     * @return bool
     */
    public function supportsRequest(RequestInterface $request): bool
    {
        return $request->getType() == $this->getSupportedType();
    }

    /**
     * @return string
     */
    public function getSupportedType(): string
    {
        return 'test';
    }

    /**
     * @return string - FQN class name
     */
    public function getExpectedRequestPayload(): string
    {
        return TestPayload::class;
    }

    /**
     * @param Request|RequestInterface $request
     * @param Response|ResponseInterface $response
     *
     * @return Promise
     */
    public function process(RequestInterface $request, ResponseInterface $response): Promise
    {
        return new Success();
    }
}
