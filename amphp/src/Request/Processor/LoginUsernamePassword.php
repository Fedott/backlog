<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Payload\UsernamePasswordPayload;
use Fedot\Backlog\Request\Request;

class LoginUsernamePassword implements ProcessorInterface
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
     * @return string
     */
    public function getSupportedType(): string
    {
        return 'login-username-password';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return UsernamePasswordPayload::class;
    }

    /**
     * @param Request $request
     */
    public function process(Request $request)
    {
        // TODO: Implement process() method.
    }
}
