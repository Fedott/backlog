<?php

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\MoveStoryPayload;
use Fedot\Backlog\Request\Processor\MoveStory;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\StoriesRepository;
use Tests\Fedot\Backlog\BaseTestCase;

class MoveStoryTest extends BaseTestCase
{
    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = new MoveStory(
            $this->createMock(StoriesRepository::class)
        );
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request();
        $request1->type = 'move-story';

        $request2 = new Request();
        $request2->type = 'get-stories';

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
        $storiesRepositoryMock = $this->createMock(StoriesRepository::class);

        $request = new Request();
        $request->id = 33;
        $request->type = 'move-story';
        $request->payload = new MoveStoryPayload();
        $request->payload->storyId = 'target-story-id';
        $request->payload->beforeStoryId = 'before-story-id';
        $request->setClientId(432);
        $request->setResponseSender($responseSenderMock);

        $processor = new MoveStory($storiesRepositoryMock);

        $storiesRepositoryMock->expects($this->once())
            ->method('move')
            ->with('target-story-id', 'before-story-id')
            ->willReturn(new Success(true))
        ;

        $responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response){
                $this->assertEquals(33, $response->requestId);
                $this->assertEquals('story-moved', $response->type);

                $responsePayload = $response->payload;
                $this->assertInstanceOf(EmptyPayload::class, $responsePayload);

                return true;
            }), $this->equalTo(432))
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }
}
