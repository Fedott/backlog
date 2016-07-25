<?php
namespace Tests\Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Payload\LoginFailedPayload;
use Fedot\Backlog\Payload\LoginSuccessPayload;
use Fedot\Backlog\Payload\UsernamePasswordPayload;
use Fedot\Backlog\Request\Processor\LoginUsernamePassword;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Tests\Fedot\Backlog\BaseTestCase;

class LoginUsernamePasswordTest extends BaseTestCase
{
    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = new LoginUsernamePassword();
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
        $processor = new LoginUsernamePassword();

        $this->assertEquals(UsernamePasswordPayload::class, $processor->getExpectedRequestPayload());
    }

    public function testProcessSuccess()
    {
        $responseSenderMock = $this->createMock(ResponseSender::class);

        $processor = new LoginUsernamePassword();

        $request = new Request();
        $request->id = 34;
        $request->type = 'login-username-password';
        $request->setClientId(777);
        $request->setResponseSender($responseSenderMock);
        $request->payload = new UsernamePasswordPayload();
        $request->payload->username = 'testUser';
        $request->payload->password = 'testPassword';

        $responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response) {
                $this->assertEquals(34, $response->requestId);
                $this->assertEquals('login-success', $response->type);

                /** @var LoginSuccessPayload $response->payload */
                $this->assertInstanceOf(LoginSuccessPayload::class, $response->payload);
                $this->assertEquals('authenticated-token', $response->payload->token);
            }), $this->equalTo(777))
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }

    public function testProcessFailed()
    {
        $responseSenderMock = $this->createMock(ResponseSender::class);

        $processor = new LoginUsernamePassword();

        $request = new Request();
        $request->id = 34;
        $request->type = 'login-username-password';
        $request->setClientId(777);
        $request->setResponseSender($responseSenderMock);
        $request->payload = new UsernamePasswordPayload();
        $request->payload->username = 'testUser';
        $request->payload->password = 'testPassword';

        $responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response) {
                $this->assertEquals(34, $response->requestId);
                $this->assertEquals('login-failed', $response->type);

                /** @var LoginFailedPayload $response->payload */
                $this->assertInstanceOf(LoginFailedPayload::class, $response->payload);
                $this->assertEquals('Invalid username or password', $response->payload->error);
            }), $this->equalTo(777))
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }
}
