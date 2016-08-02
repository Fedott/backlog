<?php declare(strict_types=1);

namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Payload\LoginFailedPayload;
use Fedot\Backlog\Payload\LoginSuccessPayload;
use Fedot\Backlog\Payload\TokenPayload;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

class LoginToken implements ProcessorInterface
{
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    /**
     * LoginToken constructor.
     *
     * @param AuthenticationService                    $authenticationService
     * @param WebSocketConnectionAuthenticationService $webSocketAuthService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        WebSocketConnectionAuthenticationService $webSocketAuthService
    ) {
        $this->authenticationService = $authenticationService;
        $this->webSocketAuthService = $webSocketAuthService;
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
        return 'login-token';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return TokenPayload::class;
    }

    /**
     * @param Request $request
     */
    public function process(Request $request)
    {
        \Amp\immediately(function () use ($request){
            /** @var TokenPayload $payload */
            $payload = $request->payload;

            $response = new Response();
            $response->requestId = $request->id;

            try {
                $username = yield $this->authenticationService->authByToken(
                    $payload->token
                );

                $response->type = 'login-success';
                $response->payload = new LoginSuccessPayload();
                $response->payload->username = $username;
                $response->payload->token = $payload->token;

                $user = new User();
                $user->username = $username;

                $this->webSocketAuthService->authorizeClient($request->getClientId(), $user);
            } catch (AuthenticationException $exception) {
                $response->type = 'login-failed';
                $response->payload = new LoginFailedPayload();
                $response->payload->error = $exception->getMessage();
            }

            $request->getResponseSender()->sendResponse($response, $request->getClientId());
        });
    }
}
