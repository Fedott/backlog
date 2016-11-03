<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\StoryPayload;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\Request\Processor\CreateStory;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use PHPUnit_Framework_MockObject_MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
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

    protected function getProcessorInstance(): ProcessorInterface
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

    protected function getExpectedValidRequestType(): string
    {
        return 'create-story';
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
        $request = new Request(33, 'create-story', 432, (array)$payload);
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
        $uuidMock = $this->createMock(Uuid::class);

        $processor = $this->getProcessorInstance();

        $project = new Project();
        $project->id = 'project-id';

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $payload = new StoryPayload();
        $payload->story = new Story();
        $payload->story->title = 'story title';
        $payload->story->text = 'story text';
        $payload->projectId = 'project-id';
        $request = new Request(33, 'create-story', 432, (array)$payload);
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
            ->willReturn(new Success(false))
        ;

        $response = new Response($request->getId(), $request->getClientId());

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals(33, $response->getRequestId());
        $this->assertEquals(432, $response->getClientId());
        $this->assertEquals('error', $response->getType());
        $this->assertEquals("Story id 'UUIDSuperUnique' already exists", $response->getPayload()['message']);
    }
}
