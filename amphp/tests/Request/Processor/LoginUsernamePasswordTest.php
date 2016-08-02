<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Failure;
use Amp\Success;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Payload\LoginFailedPayload;
use Fedot\Backlog\Payload\LoginSuccessPayload;
use Fedot\Backlog\Payload\UsernamePasswordPayload;
use Fedot\Backlog\Request\Processor\LoginUsernamePassword;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Tests\Fedot\Backlog\BaseTestCase;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class LoginUsernamePasswordTest extends RequestProcessorTestCase
{
    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = new LoginUsernamePassword(
            $this->createMock(AuthenticationService::class),
            $this->createMock(WebSocketConnectionAuthenticationService::class)
        );
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request();
        $request1->type = 'login-username-password';

        $request2 = new Request();
        $request2->type = 'other';

        $request3 = new Request();

        return [
            'login-username-password type' => [$request1, true],
            'other type'                   => [$request2, false],
            'null type'                    => [$request3, false],
        ];
    }

    public function testGetExpectedRequestPayload()
    {
        $processor = new LoginUsernamePassword(
            $this->createMock(AuthenticationService::class),
            $this->createMock(WebSocketConnectionAuthenticationService::class)
        );

        $this->assertEquals(UsernamePasswordPayload::class, $processor->getExpectedRequestPayload());
    }

    public function testProcessSuccess()
    {
        $authMock = $this->createMock(AuthenticationService::class);
        $webSocketAuthMock = $this->createMock(WebSocketConnectionAuthenticationService::class);
        $this->responseSenderMock = $this->createMock(ResponseSender::class);

        $processor = new LoginUsernamePassword($authMock, $webSocketAuthMock);

        $request = new Request();
        $request->id = 34;
        $request->type = 'login-username-password';
        $request->setClientId(777);
        $request->setResponseSender($this->responseSenderMock);
        $request->payload = new UsernamePasswordPayload();
        $request->payload->username = 'testUser';
        $request->payload->password = 'testPassword';

        $user = new User();
        $user->username = 'testUser';
        $user->password = '$2y$10$kEYXDhRhNmS1mk226hurv.i23tmnFXuqa1LCMG7UoyhZ3nF/PK7a2';
        $authMock->expects($this->once())
            ->method('authByUsernamePassword')
            ->with('testUser', 'testPassword')
            ->willReturn(new Success([$user, 'authenticated-token']))
        ;

        $webSocketAuthMock->expects($this->once())
            ->method('authorizeClient')
            ->with($this->equalTo(777), $this->equalTo($user))
        ;

        $this->responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response) {
                $this->assertEquals(34, $response->requestId);
                $this->assertEquals('login-success', $response->type);

                /** @var LoginSuccessPayload $response->payload */
                $this->assertInstanceOf(LoginSuccessPayload::class, $response->payload);
                $this->assertEquals('authenticated-token', $response->payload->token);
                $this->assertEquals('testUser', $response->payload->username);

                return true;
            }), $this->equalTo(777))
        ;

        $this->startProcessMethod($processor, $request);
    }

    public function testProcessFailed()
    {
        $this->responseSenderMock = $this->createMock(ResponseSender::class);
        $authMock = $this->createMock(AuthenticationService::class);
        $webSocketAuthMock = $this->createMock(WebSocketConnectionAuthenticationService::class);

        $processor = new LoginUsernamePassword($authMock, $webSocketAuthMock);

        $request = new Request();
        $request->id = 34;
        $request->type = 'login-username-password';
        $request->setClientId(777);
        $request->setResponseSender($this->responseSenderMock);
        $request->payload = new UsernamePasswordPayload();
        $request->payload->username = 'testUser';
        $request->payload->password = 'testPassword';

        $authMock->expects($this->once())
            ->method('authByUsernamePassword')
            ->with('testUser', 'testPassword')
            ->willReturn(new Failure(new AuthenticationException("Invalid username or password")))
        ;

        $webSocketAuthMock->expects($this->never())
            ->method('authorizeClient')
        ;

        $this->responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response) {
                $this->assertEquals(34, $response->requestId);
                $this->assertEquals('login-failed', $response->type);

                /** @var LoginFailedPayload $response->payload */
                $this->assertInstanceOf(LoginFailedPayload::class, $response->payload);
                $this->assertEquals('Invalid username or password', $response->payload->error);

                return true;
            }), $this->equalTo(777))
        ;

        $this->startProcessMethod($processor, $request);
    }
}
