<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Middleware;

use Amp\Success;
use Fedot\Backlog\Middleware\PayloadParser;
use Fedot\Backlog\SerializerService;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Tests\Fedot\Backlog\BaseTestCase;
use Tests\Fedot\Backlog\Stubs\TestPayload;

class PayloadParserTest extends BaseTestCase
{
    public function testInvoke()
    {
        $request = new Request(1, 'test', 3, ['test' => 'test']);
        $response = new Response(1, 3);
        $testPayload = new TestPayload();

        $serializerMock = $this->createMock(SerializerService::class);
        $serializerMock
            ->expects($this->once())
            ->method('parsePayload')
            ->with($request)
            ->willReturn($testPayload)
        ;

        $payloadParserMiddleware = new PayloadParser($serializerMock);

        $responsePromise = $payloadParserMiddleware($request, $response, function (RequestInterface $request, ResponseInterface $response) use ($testPayload) {
            $this->assertEquals($testPayload, $request->getAttribute('payloadObject'));

            return new Success($response);
        });

        $actualResponse = \Amp\wait($responsePromise);
        $this->assertEquals($response, $actualResponse);
    }
}
