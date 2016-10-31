<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Request\Processor\Ping;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class PingTest extends RequestProcessorTestCase
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
        $request1 = new Request(1, 1, 'ping');

        $request2 = new Request(1, 1, 'other');

        $request3 = new Request(1, 1, '');

        return [
            'ping type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    public function testProcess()
    {
        $this->responseSenderMock = $this->createMock(ResponseSender::class);

        $request = new Request(321, 777, 'ping');
        $response = new Response(321, 777);

        $processor = new Ping();

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals(321, $response->getRequestId());
        $this->assertEquals(777, $response->getClientId());
        $this->assertEquals('pong', $response->getType());
        $this->assertEquals(true, $response->getPayload()['pong']);
    }
}
