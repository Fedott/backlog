<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\MoveStoryPayload;
use Fedot\Backlog\Request\Processor\MoveStory;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class MoveStoryTest extends RequestProcessorTestCase
{
    /**
     * @return MoveStory
     */
    protected function getProcessorInstance()
    {
        $this->initProcessorMocks();

        $processor = new MoveStory($this->storiesRepositoryMock);

        return $processor;
    }

    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = $this->getProcessorInstance();
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
        $processor = $this->getProcessorInstance();

        $request = new Request();
        $request->id = 33;
        $request->type = 'move-story';
        $request->payload = new MoveStoryPayload();
        $request->payload->storyId = 'target-story-id';
        $request->payload->beforeStoryId = 'before-story-id';
        $request->payload->projectId = 'project-id';
        $request->setClientId(432);
        $request->setResponseSender($this->responseSenderMock);

        $this->storiesRepositoryMock->expects($this->once())
            ->method('moveByIds')
            ->with('project-id', 'target-story-id', 'before-story-id')
            ->willReturn(new Success(true))
        ;

        $this->responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response){
                $this->assertEquals(33, $response->requestId);
                $this->assertEquals('story-moved', $response->type);

                $responsePayload = $response->payload;
                $this->assertInstanceOf(EmptyPayload::class, $responsePayload);

                return true;
            }), $this->equalTo(432))
        ;

        $this->startProcessMethod($processor, $request);
    }
}
