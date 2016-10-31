<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\StoryPayload;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\Request\Processor\CreateStory;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use PHPUnit_Framework_MockObject_MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Symfony\Component\Serializer\Serializer;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class CreateStoryTest extends RequestProcessorTestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|UuidFactory
     */
    protected $uuidFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ProjectsRepository
     */
    protected $projectRepositoryMock;

    /**
     * @return CreateStory
     */
    protected function getProcessorInstance()
    {
        $this->initProcessorMocks();

        $this->uuidFactoryMock = $this->createMock(UuidFactory::class);
        $this->projectRepositoryMock = $this->createMock(ProjectsRepository::class);

        return new CreateStory($this->storiesRepositoryMock,
            $this->projectRepositoryMock,
            $this->uuidFactoryMock,
            $this->webSocketAuthServiceMock
        );
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
        $request1 = new Request(1, 1, 'create-story');
        $request2 = new Request(1, 1, 'get-stories');
        $request3 = new Request(1, 1, '');

        return [
            'create-story type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();

        $project = new Project();
        $project->id = 'project-id';


        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $uuidMock = $this->createMock(Uuid::class);

        $payload = new StoryPayload();
        $payload->story = new Story();
        $payload->story->title = 'story title';
        $payload->story->text = 'story text';
        $payload->projectId = 'project-id';
        $request = new Request(33, 432, 'create-story', (array) $payload);
        $request = $request->withAttribute('payloadObject', $payload);

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
            ->with($this->equalTo($project), $this->callback(function (Story $story) {
                $this->assertEquals('UUIDSuperUnique', $story->id);
                $this->assertEquals('story title', $story->title);
                $this->assertEquals('story text', $story->text);

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        $response = new Response($request->getId(), $request->getClientId());

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals(33, $response->getRequestId());
        $this->assertEquals(432, $response->getClientId());
        $this->assertEquals('story-created', $response->getType());
        $this->assertEquals('UUIDSuperUnique', $response->getPayload()['id']);
        $this->assertEquals('story title', $response->getPayload()['title']);
        $this->assertEquals('story text', $response->getPayload()['text']);
    }

    public function testProcessWithError()
    {
        $this->markTestIncomplete('Need fix');
        $this->responseSenderMock = $this->createMock(ResponseSender::class);
        $uuidMock = $this->createMock(Uuid::class);

        $processor = $this->getProcessorInstance();

        $project = new Project();
        $project->id = 'project-id';

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $request = new Request();
        $request->id = 33;
        $request->type = 'create-story';
        $request->payload = new StoryPayload();
        $request->payload->story = new Story();
        $request->payload->story->title = 'story title';
        $request->payload->story->text = 'story text';
        $request->payload->projectId = 'project-id';
        $request->setClientId(432);
        $request->setResponseSender($this->responseSenderMock);

        $this->serializerMock
            ->method('denormalize')
            ->willReturn($request->payload->story)
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
            ->with($this->equalTo($project), $this->callback(function (Story $story) {
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
