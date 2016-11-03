<?php declare(strict_types=1);

namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Payload\LoginFailedPayload;
use Fedot\Backlog\Payload\LoginSuccessPayload;
use Fedot\Backlog\Payload\TokenPayload;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

class LoginToken extends AbstractProcessor
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

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
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

            $user = new User();
            $user->username = $username;

            $this->webSocketAuthService->authorizeClient($request->getClientId(), $user);
        } catch (AuthenticationException $exception) {
            $response = $response->withType('login-failed');
            $loginFailedPayload = new LoginFailedPayload();
            $loginFailedPayload->error = $exception->getMessage();
            $response = $response->withPayload((array) $loginFailedPayload);
        }

        $promisor->succeed($response);
    }
}
