<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Request\Processor\Ping;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class PingTest extends RequestProcessorTestCase
{
    protected function getProcessorInstance(): ProcessorInterface
    {
        return new Ping();
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'ping';
    }

    public function testProcess()
    {
        $request = new Request(321, 'ping', 777);
        $response = new Response(321, 777);

        $processor = new Ping();

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 321, 777, 'pong');

        $this->assertEquals(true, $response->getPayload()['pong']);
    }
}
