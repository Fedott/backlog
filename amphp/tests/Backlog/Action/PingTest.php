<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Action;

use Fedot\Backlog\Action\EmptyPayload;
use Fedot\Backlog\Action\Ping;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\ActionTestCase;

class PingTest extends ActionTestCase
{
    protected function getProcessorInstance(): ActionInterface
    {
        return new Ping();
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'ping';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return EmptyPayload::class;
    }

    public function testProcess()
    {
        $request = new Request(321, 'ping', 777);
        $response = new Response(321, 777);

        $processor = new Ping();

        /** @var Response $response */
        $response = \Amp\Promise\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 321, 777, 'pong');

        $this->assertEquals(true, $response->getPayload()['pong']);
    }
}
