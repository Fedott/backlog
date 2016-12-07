<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Payload\LoginFailedPayload;
use Fedot\Backlog\Payload\LoginSuccessPayload;
use Fedot\Backlog\Payload\UsernamePasswordPayload;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

class LoginUsernamePassword extends AbstractProcessor
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

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var UsernamePasswordPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        try {
            list($user, $token) = yield $this->authenticationService->authByUsernamePassword(
                $payload->username,
                $payload->password
            );

            $newPayload = new LoginSuccessPayload();
            $newPayload->username = $user->username;
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

        $promisor->succeed($response);
    }
}
