<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Stubs;

use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Request\Request;

class TestProcessor implements ProcessorInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool
    {
        return $request->type == $this->getSupportedType();
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
     */
    public function process(Request $request)
    {
    }
}
