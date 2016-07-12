<?php
namespace Tests\Fedot\Backlog;

use Aerys\Websocket\Endpoint;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;

class ResponseSenderTest extends \PHPUnit_Framework_TestCase
{
    public function testSendResponse()
    {
        $endpointMock = $this->getMockBuilder(Endpoint::class)->getMock();

        $sender = new ResponseSender($endpointMock);

        $response = new Response();
        $response->requestId = 777;
        $response->type = 'test';
        $response->payload = new TestPayload();
        $response->payload->field1 = 45;
        $response->payload->field3 = 'fieldData';

        $endpointMock->expects($this->once())
            ->method('send')
            ->with(543, '{"requestId":777,"type":"test","payload":{"field1":45,"field3":"fieldData"}}')
        ;

        $sender->sendResponse($response, 543);
    }
}
