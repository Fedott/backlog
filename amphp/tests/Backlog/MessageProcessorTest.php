<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Amp\Loop;
use Amp\Success;
use Fedot\Backlog\Infrastructure\Middleware\Runner;
use Fedot\Backlog\Infrastructure\Middleware\RunnerFactory;
use Fedot\Backlog\MessageProcessor;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;

class MessageProcessorTest extends BaseTestCase
{
    public static function setUpBeforeClass()
    {
        Loop::set(new Loop\NativeDriver());
    }

    public static function tearDownAfterClass()
    {
        Loop::stop();
    }

    public function testProcessMessage()
    {
        $serializerMock = $this->createMock(SerializerInterface::class);
        $middlewareRunnerFactoryMock = $this->createMock(RunnerFactory::class);
        $middlewareRunnerMock = $this->createMock(Runner::class);
        $endpointMock = $this->createMock(Endpoint::class);

        $webSocketServer = new MessageProcessor($middlewareRunnerFactoryMock, $serializerMock);

        $request = new Request(1, 'test', 123, ['test' => 'test']);

        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with("jj", Request::class, 'json')
            ->willReturn($request)
        ;
        $middlewareRunnerFactoryMock->expects($this->once())
            ->method('newInstance')
            ->willReturn($middlewareRunnerMock)
        ;
        $middlewareRunnerMock->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (RequestInterface $request) {
                $this->assertEquals(1, $request->getId());
                $this->assertEquals(123, $request->getClientId());
                $this->assertEquals('test', $request->getType());

                return true;
            }), $this->callback(function (ResponseInterface $response) {
                $this->assertEquals(1, $response->getRequestId());
                $this->assertEquals(123, $response->getClientId());

                return true;
            }))
            ->willReturn(new Success(new Response($request->getId(), $request->getClientId(), 'test-response')))
        ;

        $endpointMock->expects($this->once())
            ->method('send')
            ->with('{"requestId":1,"type":"test-response","payload":[]}', 123)
        ;

        Loop::run(function () use ($webSocketServer, $endpointMock) {
            yield from $webSocketServer->processMessage($endpointMock, 123, "jj");
        });
    }

    public function testProcessMessageNotDirectResponse()
    {
        $serializerMock = $this->createMock(SerializerInterface::class);
        $middlewareRunnerFactoryMock = $this->createMock(RunnerFactory::class);
        $middlewareRunnerMock = $this->createMock(Runner::class);
        $endpointMock = $this->createMock(Endpoint::class);

        $webSocketServer = new MessageProcessor($middlewareRunnerFactoryMock, $serializerMock);

        $request = new Request(1, 'test', 123, ['test' => 'test']);

        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with("jj", Request::class, 'json')
            ->willReturn($request)
        ;
        $middlewareRunnerFactoryMock->expects($this->once())
            ->method('newInstance')
            ->willReturn($middlewareRunnerMock)
        ;
        $middlewareResponse = new Response(
            $request->getId(),
            $request->getClientId(),
            'test-response',
            [],
            false
        );
        $middlewareRunnerMock->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (RequestInterface $request) {
                $this->assertEquals(1, $request->getId());
                $this->assertEquals(123, $request->getClientId());
                $this->assertEquals('test', $request->getType());

                return true;
            }), $this->callback(function (ResponseInterface $response) {
                $this->assertEquals(1, $response->getRequestId());
                $this->assertEquals(123, $response->getClientId());

                return true;
            }))
            ->willReturn(new Success($middlewareResponse))
        ;

        $endpointMock->expects($this->once())
            ->method('broadcast')
            ->with('{"requestId":1,"type":"test-response","payload":[]}')
        ;

        Loop::run(function () use ($webSocketServer, $endpointMock) {
            yield from $webSocketServer->processMessage($endpointMock, 123, "jj");
        });
    }

    public function testProcessMessageException()
    {
        $serializerMock = $this->createMock(SerializerInterface::class);
        $middlewareRunnerFactoryMock = $this->createMock(RunnerFactory::class);
        $middlewareRunnerMock = $this->createMock(Runner::class);
        $endpointMock = $this->createMock(Endpoint::class);

        $webSocketServer = new MessageProcessor($middlewareRunnerFactoryMock, $serializerMock);

        $request = new Request(1, 'test', 123, ['test' => 'test']);

        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with("jj", Request::class, 'json')
            ->willReturn($request)
        ;
        $middlewareRunnerFactoryMock->expects($this->once())
            ->method('newInstance')
            ->willReturn($middlewareRunnerMock)
        ;
        $middlewareRunnerMock->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function (RequestInterface $request) {
                $this->assertEquals(1, $request->getId());
                $this->assertEquals(123, $request->getClientId());
                $this->assertEquals('test', $request->getType());

                return true;
            }), $this->callback(function (ResponseInterface $response) {
                $this->assertEquals(1, $response->getRequestId());
                $this->assertEquals(123, $response->getClientId());

                return true;
            }))
            ->willThrowException(new \RuntimeException("Atata"))
        ;

        $endpointMock->expects($this->once())
            ->method('send')
            ->with('{"requestId":1,"type":"internal-server-error","payload":{"message":"Atata"}}', 123)
        ;

        Loop::run(function () use ($webSocketServer, $endpointMock) {
            yield from $webSocketServer->processMessage($endpointMock, 123, "jj");
        });
    }
}
