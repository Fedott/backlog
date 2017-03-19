<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Action;

use Amp\Failure;
use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\User\Login\UsernamePasswordPayload;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\ActionTestCase;

class LoginUsernamePasswordTest extends ActionTestCase
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

        return new \Fedot\Backlog\Action\User\Login\LoginUsernamePassword(
            $this->authMock,
            $this->webSocketAuthMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'login-username-password';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return UsernamePasswordPayload::class;
    }

    public function testProcessSuccess()
    {
        $processor = $this->getProcessorInstance();

        $payload = new UsernamePasswordPayload();
        $payload->username = 'testUser';
        $payload->password = 'testPassword';

        $request = $this->makeRequest(34, 777, 'login-username-password', $payload);
        $response = $this->makeResponse($request);

        $user = new User(
            'testUser',
            '$2y$10$kEYXDhRhNmS1mk226hurv.i23tmnFXuqa1LCMG7UoyhZ3nF/PK7a2'
        );
        $this->authMock->expects($this->once())
            ->method('authByUsernamePassword')
            ->with('testUser', 'testPassword')
            ->willReturn(new Success([$user, 'authenticated-token']))
        ;

        $this->webSocketAuthMock->expects($this->once())
            ->method('authorizeClient')
            ->with($this->equalTo(777), $this->equalTo($user))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 34, 777, 'login-success');

        $this->assertEquals('authenticated-token', $response->getPayload()['token']);
        $this->assertEquals('testUser', $response->getPayload()['username']);
    }

    public function testProcessFailed()
    {
        $processor = $this->getProcessorInstance();

        $payload = new UsernamePasswordPayload();
        $payload->username = 'testUser';
        $payload->password = 'testPassword';

        $request = $this->makeRequest(34, 777, 'login-username-password', $payload);
        $response = $this->makeResponse($request);

        $this->authMock->expects($this->once())
            ->method('authByUsernamePassword')
            ->with('testUser', 'testPassword')
            ->willReturn(new Failure(new AuthenticationException("Invalid username or password")))
        ;

        $this->webSocketAuthMock->expects($this->never())
            ->method('authorizeClient')
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 34, 777, 'login-failed');

        $this->assertEquals('Invalid username or password', $response->getPayload()['error']);
    }
}
