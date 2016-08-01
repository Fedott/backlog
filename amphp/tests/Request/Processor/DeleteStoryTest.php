<?php
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Request\Processor\DeleteStory;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\DeleteStoryPayload;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Response\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class DeleteStoryTest extends RequestProcessorTestCase
{
    /**
     * @return DeleteStory
     */
    protected function getProcessorInstance()
    {
        $this->initProcessorMocks();

        $processor = new DeleteStory($this->storiesRepositoryMock);

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
        $request1->type = 'delete-story';

        $request2 = new Request();
        $request2->type = 'other';

        $request3 = new Request();

        return [
            'delete-story type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();

        $request = new Request();
        $request->id = 34;
        $request->type = 'delete-story';
        $request->setClientId(777);
        $request->setResponseSender($this->responseSenderMock);
        $request->payload = new DeleteStoryPayload();
        $request->payload->storyId = 'storyId4534';

        $this->storiesRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('storyId4534'))
            ->willReturn(new Success(true))
        ;

        $this->responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->willReturnCallback(function (Response $response, $clientId = null) {
                $this->assertEquals(777, $clientId);
                $this->assertEquals(34, $response->requestId);
                $this->assertEquals('story-deleted', $response->type);

                /** @var EmptyPayload $response->payload */
                $this->assertInstanceOf(EmptyPayload::class, $response->payload);
            })
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }
}
