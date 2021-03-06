<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\User\Login;

use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

class LoginToken extends AbstractAction
{
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    public function __construct(
        AuthenticationService $authenticationService,
        WebSocketConnectionAuthenticationService $webSocketAuthService
    ) {
        $this->authenticationService = $authenticationService;
        $this->webSocketAuthService = $webSocketAuthService;
    }

    public function getSupportedType(): string
    {
        return 'login-token';
    }

    public function getExpectedRequestPayload(): string
    {
        return TokenPayload::class;
    }

    protected function execute(RequestInterface $request, ResponseInterface $response)
    {
        /** @var TokenPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        try {
            $username = yield $this->authenticationService->authByToken(
                $payload->token
            );

            $newPayload = new LoginSuccessPayload();
            $newPayload->username = $username;
            $newPayload->token = $payload->token;

            $response = $response->withType('login-success');
            $response = $response->withPayload((array) $newPayload);

            /** @var User $user */
            $user = yield $this->authenticationService->findUserByUsername($username);

            $this->webSocketAuthService->authorizeClient($request->getClientId(), $user);
        } catch (AuthenticationException $exception) {
            $response = $response->withType('login-failed');
            $loginFailedPayload = new LoginFailedPayload();
            $loginFailedPayload->error = $exception->getMessage();
            $response = $response->withPayload((array) $loginFailedPayload);
        }

        return $response;
    }
}
