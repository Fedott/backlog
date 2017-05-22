<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\User\Login;

use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

class LoginUsernamePassword extends AbstractAction
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
        return 'login-username-password';
    }

    public function getExpectedRequestPayload(): string
    {
        return UsernamePasswordPayload::class;
    }

    protected function execute(RequestInterface $request, ResponseInterface $response)
    {
        /** @var UsernamePasswordPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        try {
            /** @var User $user */
            /** @var string $token */
            list($user, $token) = yield $this->authenticationService->authByUsernamePassword(
                $payload->username,
                $payload->password
            );

            $newPayload = new LoginSuccessPayload();
            $newPayload->username = $user->getUsername();
            $newPayload->token = $token;
            $response = $response->withType('login-success');
            $response = $response->withPayload((array) $newPayload);

            $this->webSocketAuthService->authorizeClient($request->getClientId(), $user);
        } catch (AuthenticationException $exception) {
            $newPayload = new LoginFailedPayload();
            $newPayload->error = $exception->getMessage();

            $response = $response->withType('login-failed');
            $response = $response->withPayload((array) $newPayload);
        }

        return $response;
    }
}
