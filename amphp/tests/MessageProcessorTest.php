<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Fedot\Backlog\MessageProcessor;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\SerializerService;

class MessageProcessorTest extends BaseTestCase
{
    public function testProcessMessage()
    {
        $serializerServiceMock = $this->createMock(SerializerService::class);
        $requestProcessorMock = $this->createMock(RequestProcessorManager::class);
        $responseSenderMock = $this->createMock(ResponseSender::class);

        $webSocketServer = new MessageProcessor($serializerServiceMock, $requestProcessorMock);

        $request = new Request();

        $serializerServiceMock->expects($this->once())
            ->method('parseRequest')
            ->with("jj")
            ->willReturn($request)
        ;
        $requestProcessorMock->expects($this->once())
            ->method('process')
            ->with($request)
        ;

        $webSocketServer->processMessage(123, "jj", $responseSenderMock);

        $this->assertEquals(123, $request->getClientId());

        $responseSender = $request->getResponseSender();
        $this->assertInstanceOf(ResponseSender::class, $responseSender);
        $this->assertEquals($responseSenderMock, $responseSender);
    }

    public function testProcessMessageWithError()
    {

        $serializerServiceMock = $this->createMock(SerializerService::class);
        $requestProcessorMock = $this->createMock(RequestProcessorManager::class);
        $responseSenderMock = $this->createMock(ResponseSender::class);

        $webSocketServer = new MessageProcessor($serializerServiceMock, $requestProcessorMock);

        $request = new Request();
        $request->id = 434;
        $request->type = 'test-error';
        $request->setClientId(675);

        $serializerServiceMock->expects($this->once())
            ->method('parseRequest')
            ->with("jj")
            ->willReturn($request)
        ;
        $serializerServiceMock->expects($this->once())
            ->method('parsePayload')
            ->with($request)
            ->willThrowException(new \RuntimeException("Not found payload type: qwe"))
        ;
        $requestProcessorMock->expects($this->never())
            ->method('process')
        ;

        $responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with(
                $this->callback(function (Response $response) {
                    $this->assertEquals('error', $response->type);
                    $this->assertEquals(434, $response->requestId);

                    /** @var ErrorPayload $response->payload */
                    $this->assertInstanceOf(ErrorPayload::class, $response->payload);
                    $this->assertEquals('Not found payload type: qwe', $response->payload->message);

                    return true;
                }),
                $this->equalTo(675)
            )
        ;

        $webSocketServer->processMessage(675, "jj", $responseSenderMock);
    }
}
