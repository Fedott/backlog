<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Action;

use Amp\Failure;
use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\User\Login\LoginToken;
use Fedot\Backlog\Action\User\Login\TokenPayload;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\ActionTestCase;

class LoginTokenTest extends ActionTestCase
{
    /**
     * @var AuthenticationService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $authMock;

    /**
     * @var WebSocketConnectionAuthenticationService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $webSocketAuthMock;

    protected function getProcessorInstance(): ActionInterface
    {
        $this->authMock = $this->createMock(AuthenticationService::class);
        $this->webSocketAuthMock = $this->createMock(WebSocketConnectionAuthenticationService::class);

        return new LoginToken($this->authMock, $this->webSocketAuthMock);
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'login-token';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return TokenPayload::class;
    }

    public function testGetExpectedRequestPayload()
    {
        $processor = new LoginToken(
            $this->createMock(AuthenticationService::class),
            $this->createMock(WebSocketConnectionAuthenticationService::class)
        );

        $this->assertEquals(TokenPayload::class, $processor->getExpectedRequestPayload());
    }

    public function testProcessSuccess()
    {
        $processor = $this->getProcessorInstance();

        $payload = new TokenPayload();
        $payload->token = 'auth-token';

        $request = $this->makeRequest(34, 777, 'auth-token', $payload);
        $response = $this->makeResponse($request);

        $this->authMock->expects($this->once())
            ->method('authByToken')
            ->with('auth-token')
            ->willReturn(new Success('testUser'))
        ;

        $this->authMock->expects($this->once())
            ->method('findUserByUsername')
            ->with('testUser')
            ->willReturn(new Success(new User('testUser', 'hash')))
        ;

        $this->webSocketAuthMock->expects($this->once())
            ->method('authorizeClient')
            ->with($this->equalTo(777), $this->callback(function (User $user) {
                $this->assertEquals('testUser', $user->getUsername());

                return true;
            }))
        ;

        /** @var Response $response */
        $response = \Amp\Promise\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 34, 777, 'login-success');

        $this->assertEquals('auth-token', $response->getPayload()['token']);
        $this->assertEquals('testUser', $response->getPayload()['username']);
    }

    public function testProcessFailed()
    {
        $processor = $this->getProcessorInstance();

        $payload = new TokenPayload();
        $payload->token = 'auth-token';

        $request = $this->makeRequest(34, 777, 'login-token', $payload);
        $response = $this->makeResponse($request);

        $this->authMock->expects($this->once())
            ->method('authByToken')
            ->with('auth-token')
            ->willReturn(new Failure(new AuthenticationException("Invalid or expired token")))
        ;

        $this->webSocketAuthMock->expects($this->never())
            ->method('authorizeClient')
        ;

        /** @var Response $response */
        $response = \Amp\Promise\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 34, 777, 'login-failed');

        $this->assertEquals('Invalid or expired token', $response->getPayload()['error']);
    }
}
