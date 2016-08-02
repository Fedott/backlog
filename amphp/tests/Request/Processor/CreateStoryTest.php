<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Request\Processor\CreateStory;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\StoriesRepository;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\Fedot\Backlog\BaseTestCase;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class CreateStoryTest extends RequestProcessorTestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|UuidFactory
     */
    protected $uuidFactoryMock;

    /**
     * @return CreateStory
     */
    protected function getProcessorInstance()
    {
        $this->initProcessorMocks();

        $this->uuidFactoryMock = $this->createMock(UuidFactory::class);

        return new CreateStory($this->storiesRepositoryMock, $this->uuidFactoryMock, $this->webSocketAuthServiceMock);
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
        $this->responseSenderMock = $this->createMock(ResponseSender::class);
        $uuidMock = $this->createMock(Uuid::class);

        $processor = $this->getProcessorInstance();

        $request = new Request();
        $request->id = 33;
        $request->type = 'create-story';
        $request->payload = new Story();
        $request->payload->title = 'story title';
        $request->payload->text = 'story text';
        $request->setClientId(432);
        $request->setResponseSender($this->responseSenderMock);

        $user = new User();

        $this->webSocketAuthServiceMock
            ->expects($this->once())
            ->method('getAuthorizedUserForClient')
            ->with($this->equalTo(432))
            ->willReturn($user)
        ;

        $this->uuidFactoryMock
            ->expects($this->once())
            ->method('uuid4')
            ->willReturn($uuidMock)
        ;

        $uuidMock->expects($this->once())
            ->method('toString')
            ->willReturn('UUIDSuperUnique')
        ;

        $this->storiesRepositoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($user), $this->callback(function (Story $story) {
                $this->assertEquals('UUIDSuperUnique', $story->id);
                $this->assertEquals('story title', $story->title);
                $this->assertEquals('story text', $story->text);

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        $this->responseSenderMock->expects($this->once())
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

        $this->startProcessMethod($processor, $request);
    }

    public function testProcessWithError()
    {
        $this->responseSenderMock = $this->createMock(ResponseSender::class);
        $uuidMock = $this->createMock(Uuid::class);

        $processor = $this->getProcessorInstance();

        $request = new Request();
        $request->id = 33;
        $request->type = 'create-story';
        $request->payload = new Story();
        $request->payload->title = 'story title';
        $request->payload->text = 'story text';
        $request->setClientId(432);
        $request->setResponseSender($this->responseSenderMock);

        $user = new User();

        $this->webSocketAuthServiceMock
            ->expects($this->once())
            ->method('getAuthorizedUserForClient')
            ->with($this->equalTo(432))
            ->willReturn($user)
        ;

        $this->uuidFactoryMock
            ->expects($this->once())
            ->method('uuid4')
            ->willReturn($uuidMock)
        ;

        $uuidMock->expects($this->once())
            ->method('toString')
            ->willReturn('UUIDSuperUnique')
        ;

        $this->storiesRepositoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($user), $this->callback(function (Story $story) {
                $this->assertEquals('UUIDSuperUnique', $story->id);
                $this->assertEquals('story title', $story->title);
                $this->assertEquals('story text', $story->text);

                return true;
            }))
            ->willReturn(new Success(false))
        ;

        $this->responseSenderMock->expects($this->once())
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

        $this->startProcessMethod($processor, $request);
    }
}
