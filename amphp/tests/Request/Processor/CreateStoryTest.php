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
use Ramsey\Uuid\UuidFactory;
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
        $processor = new CreateStory(
            $this->createMock(StoriesRepository::class),
            $this->createMock(UuidFactory::class)
        );
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
        $uuidFactoryMock = $this->createMock(UuidFactory::class);

        $request = new Request();
        $request->id = 33;
        $request->type = 'create-story';
        $request->payload = new Story();
        $request->payload->title = 'story title';
        $request->payload->text = 'story text';
        $request->setClientId(432);
        $request->setResponseSender($responseSenderMock);

        $processor = new CreateStory($storiesRepositoryMock, $uuidFactoryMock);

        $uuidFactoryMock
            ->expects($this->once())
            ->method('uuid4')
            ->willReturn('UUIDSuperUnique')
        ;

        $storiesRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Story $story) {
                $this->assertEquals('UUIDSuperUnique', $story->id);
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
                $this->assertEquals('UUIDSuperUnique', $responsePayload->id);
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
        $uuidFactoryMock = $this->createMock(UuidFactory::class);

        $request = new Request();
        $request->id = 33;
        $request->type = 'create-story';
        $request->payload = new Story();
        $request->payload->title = 'story title';
        $request->payload->text = 'story text';
        $request->setClientId(432);
        $request->setResponseSender($responseSenderMock);

        $processor = new CreateStory($storiesRepositoryMock, $uuidFactoryMock);

        $uuidFactoryMock
            ->expects($this->once())
            ->method('uuid4')
            ->willReturn('UUIDSuperUnique')
        ;

        $storiesRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Story $story) {
                $this->assertEquals('UUIDSuperUnique', $story->id);
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
                $this->assertEquals("Story id 'UUIDSuperUnique' already exists", $responsePayload->message);

                return true;
            }), $this->equalTo(432))
        ;

        $processor->process($request);

        $this->waitAsyncCode();
    }
}