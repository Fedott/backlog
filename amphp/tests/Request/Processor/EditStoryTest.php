<?php

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Processor\EditStory;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Payload\ErrorPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\StoriesRepository;
use Tests\Fedot\Backlog\BaseTestCase;

class EditStoryTest extends BaseTestCase
{
    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = new EditStory($this->createMock(StoriesRepository::class));
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request();
        $request1->type = 'edit-story';

        $request2 = new Request();
        $request2->type = 'other';

        $request3 = new Request();

        return [
            'edit-story type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    public function testGetExpectedRequestPayload()
    {
        $processor = new EditStory($this->createMock(StoriesRepository::class));

        $this->assertEquals(Story::class, $processor->getExpectedRequestPayload());
    }

    public function testProcess()
    {
        $responseSenderMock = $this->createMock(ResponseSender::class);
        $storiesRepositoryMock = $this->createMock(StoriesRepository::class);

        $request = new Request();
        $request->id = 33;
        $request->type = 'edit-story';
        $request->payload = new Story();
        $request->payload->id = 'jgfjhfgj-erwer-dsfsd';
        $request->payload->title = 'story title';
        $request->payload->text = 'story text';
        $request->setClientId(432);
        $request->setResponseSender($responseSenderMock);

        $processor = new EditStory($storiesRepositoryMock);

        $storiesRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Story $story) {
                $this->assertEquals('jgfjhfgj-erwer-dsfsd', $story->id);
                $this->assertEquals('story title', $story->title);
                $this->assertEquals('story text', $story->text);

                return new Success(true);
            })
        ;

        $responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response){
                $this->assertEquals(33, $response->requestId);
                $this->assertEquals('story-edited', $response->type);

                /** @var Story $responsePayload */
                $responsePayload = $response->payload;
                $this->assertInstanceOf(Story::class, $responsePayload);
                $this->assertEquals('jgfjhfgj-erwer-dsfsd', $responsePayload->id);
                $this->assertEquals('story title', $responsePayload->title);
                $this->assertEquals('story text', $responsePayload->text);

                return true;
            }), $this->equalTo(432))
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }

    public function testProcessWithError()
    {
        $responseSenderMock = $this->createMock(ResponseSender::class);
        $storiesRepositoryMock = $this->createMock(StoriesRepository::class);

        $request = new Request();
        $request->id = 33;
        $request->type = 'edit-story';
        $request->payload = new Story();
        $request->payload->id = 'jgfjhfgj-erwer-dsfsd';
        $request->payload->title = 'story title';
        $request->payload->text = 'story text';
        $request->setClientId(432);
        $request->setResponseSender($responseSenderMock);

        $processor = new EditStory($storiesRepositoryMock);

        $storiesRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Story $story) {
                $this->assertEquals('jgfjhfgj-erwer-dsfsd', $story->id);
                $this->assertEquals('story title', $story->title);
                $this->assertEquals('story text', $story->text);

                return new Success(false);
            })
        ;

        $responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response){
                $this->assertEquals(33, $response->requestId);
                $this->assertEquals('error', $response->type);

                /** @var ErrorPayload $responsePayload */
                $responsePayload = $response->payload;
                $this->assertInstanceOf(ErrorPayload::class, $responsePayload);
                $this->assertEquals("Story id 'jgfjhfgj-erwer-dsfsd' do not saved", $responsePayload->message);

                return true;
            }), $this->equalTo(432))
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }
}
