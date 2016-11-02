<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Promisor;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Payload\LoginFailedPayload;
use Fedot\Backlog\Payload\LoginSuccessPayload;
use Fedot\Backlog\Payload\UsernamePasswordPayload;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;

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

    /**
     * LoginToken constructor.
     *
     * @param AuthenticationService $authenticationService
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

    protected function execute(Promisor $promisor, Request $request, Response $response)
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
