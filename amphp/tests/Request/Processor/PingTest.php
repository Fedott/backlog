<?php
namespace Tests\Fedot\Backlog\Request\Processor;

use Aerys\Websocket\Endpoint;
use Fedot\Backlog\Request\Processor\Ping;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Payload\PongPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;

class PingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = new Ping();
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request();
        $request1->type = 'ping';

        $request2 = new Request();
        $request2->type = 'other';

        $request3 = new Request();

        return [
            'ping type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    public function testProcess()
    {
        $responseSenderMock = $this->createMock(ResponseSender::class);

        $request = new Request();
        $request->id = 321;
        $request->type = 'ping';
        $request->setResponseSender($responseSenderMock);
        $request->setClientId(777);

        $responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->willReturnCallback(function (Response $response, $clientId = null) {
                $this->assertEquals(777, $clientId);
                $this->assertEquals(321, $response->requestId);
                $this->assertEquals('pong', $response->type);
                $this->assertInstanceOf(PongPayload::class, $response->payload);
            })
        ;

        $processor = new Ping();
        $actualResult = $processor->process($request);

        $this->assertTrue($actualResult);
    }
}
