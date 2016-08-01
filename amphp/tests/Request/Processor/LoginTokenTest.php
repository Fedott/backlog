<?php

namespace Tests\Fedot\Backlog\Request\Processor;


use Amp\Failure;
use Amp\Success;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Payload\LoginFailedPayload;
use Fedot\Backlog\Payload\LoginSuccessPayload;
use Fedot\Backlog\Payload\TokenPayload;
use Fedot\Backlog\Request\Processor\LoginToken;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Tests\Fedot\Backlog\BaseTestCase;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class LoginTokenTest extends RequestProcessorTestCase
{
    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = new LoginToken(
            $this->createMock(AuthenticationService::class),
            $this->createMock(WebSocketConnectionAuthenticationService::class)
        );
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request();
        $request1->type = 'login-token';

        $request2 = new Request();
        $request2->type = 'other';

        $request3 = new Request();

        return [
            [$request1, true],
            [$request2, false],
            [$request3, false],
        ];
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
        $authMock = $this->createMock(AuthenticationService::class);
        $webSocketAuthMock = $this->createMock(WebSocketConnectionAuthenticationService::class);
        $this->responseSenderMock = $this->createMock(ResponseSender::class);

        $processor = new LoginToken($authMock, $webSocketAuthMock);

        $request = new Request();
        $request->id = 34;
        $request->type = 'login-token';
        $request->setClientId(777);
        $request->setResponseSender($this->responseSenderMock);
        $request->payload = new TokenPayload();
        $request->payload->token = 'auth-token';

        $authMock->expects($this->once())
            ->method('authByToken')
            ->with('auth-token')
            ->willReturn(new Success('testUser'))
        ;

        $webSocketAuthMock->expects($this->once())
            ->method('authorizeClient')
            ->with($this->equalTo(777), $this->callback(function (User $user) {
                $this->assertEquals('testUser', $user->username);

                return true;
            }))
        ;

        $this->responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response) {
                $this->assertEquals(34, $response->requestId);
                $this->assertEquals('login-success', $response->type);

                /** @var LoginSuccessPayload $response->payload */
                $this->assertInstanceOf(LoginSuccessPayload::class, $response->payload);
                $this->assertEquals('auth-token', $response->payload->token);
                $this->assertEquals('testUser', $response->payload->username);

                return true;
            }), $this->equalTo(777))
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }

    public function testProcessFailed()
    {
        $this->responseSenderMock = $this->createMock(ResponseSender::class);
        $authMock = $this->createMock(AuthenticationService::class);
        $webSocketAuthMock = $this->createMock(WebSocketConnectionAuthenticationService::class);

        $processor = new LoginToken($authMock, $webSocketAuthMock);

        $request = new Request();
        $request->id = 34;
        $request->type = 'login-token';
        $request->setClientId(777);
        $request->setResponseSender($this->responseSenderMock);
        $request->payload = new TokenPayload();
        $request->payload->token = 'auth-token';

        $authMock->expects($this->once())
            ->method('authByToken')
            ->with('auth-token')
            ->willReturn(new Failure(new AuthenticationException("Invalid or expired token")))
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
                $this->assertEquals('Invalid or expired token', $response->payload->error);

                return true;
            }), $this->equalTo(777))
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }
}
