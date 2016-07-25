<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Payload\LoginFailedPayload;
use Fedot\Backlog\Payload\LoginSuccessPayload;
use Fedot\Backlog\Payload\UsernamePasswordPayload;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;

class LoginUsernamePassword implements ProcessorInterface
{
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * LoginUsernamePassword constructor.
     *
     * @param AuthenticationService $authenticationService
     */
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

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
        /** @var UsernamePasswordPayload $payload */
        $payload = $request->payload;

        $response = new Response();
        $response->requestId = $request->id;

        try {
            list($user, $token) = $this->authenticationService->authByUsernamePassword(
                $payload->username,
                $payload->password
            );

            $response->type = 'login-success';
            $response->payload = new LoginSuccessPayload();
            $response->payload->username = $user->username;
            $response->payload->token = $token;
        } catch (AuthenticationException $exception) {
            $response->type = 'login-failed';
            $response->payload = new LoginFailedPayload();
            $response->payload->error = $exception->getMessage();
        }

        $request->getResponseSender()->sendResponse($response, $request->getClientId());
    }
}
