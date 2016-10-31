<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Stubs;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;

class TestProcessor implements ProcessorInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool
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
     * @param Request $request
     * @param Response $response
     *
     * @return Promise
     */
    public function process(Request $request, Response $response): Promise
    {
        return new Success();
    }
}
