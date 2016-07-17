<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Processor\CreateStory;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Payload\ErrorPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\StoriesRepository;
use Tests\Fedot\Backlog\BaseTestCase;

class CreateStoryTest extends BaseTestCase
{
    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = new CreateStory($this->createMock(StoriesRepository::class));
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request();
        $request1->type = 'create-story';

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
        $request->type = 'create-story';
        $request->payload = new Story();
        $request->payload->number = 123;
        $request->payload->title = 'story title';
        $request->payload->text = 'story text';
        $request->setClientId(432);
        $request->setResponseSender($responseSenderMock);

        $processor = new CreateStory($storiesRepositoryMock);

        $storiesRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Story $story) {
                $this->assertEquals(123, $story->number);
                $this->assertEquals('story title', $story->title);
                $this->assertEquals('story text', $story->text);

                return new Success(true);
            })
        ;

        $responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response){
                $this->assertEquals(33, $response->requestId);
                $this->assertEquals('story-created', $response->type);

                /** @var Story $responsePayload */
                $responsePayload = $response->payload;
                $this->assertInstanceOf(Story::class, $responsePayload);
                $this->assertEquals(123, $responsePayload->number);
                $this->assertEquals('story title', $responsePayload->title);
                $this->assertEquals('story text', $responsePayload->text);

                return true;
            }), $this->equalTo(432))
        ;

        $processor->process($request);

        \Amp\tick();
        \Amp\tick();
        \Amp\tick();
    }

    public function testProcessWithError()
    {
        $responseSenderMock = $this->createMock(ResponseSender::class);
        $storiesRepositoryMock = $this->createMock(StoriesRepository::class);

        $request = new Request();
        $request->id = 33;
        $request->type = 'create-story';
        $request->payload = new Story();
        $request->payload->number = 123;
        $request->payload->title = 'story title';
        $request->payload->text = 'story text';
        $request->setClientId(432);
        $request->setResponseSender($responseSenderMock);

        $processor = new CreateStory($storiesRepositoryMock);

        $storiesRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Story $story) {
                $this->assertEquals(123, $story->number);
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
                $this->assertEquals('Story number 123 already exists', $responsePayload->message);

                return true;
            }), $this->equalTo(432))
        ;

        $processor->process($request);

        \Amp\tick();
        \Amp\tick();
        \Amp\tick();
    }
}