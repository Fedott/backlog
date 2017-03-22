<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Action;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Story\Move\MoveStory;
use Fedot\Backlog\Action\Story\Move\MoveStoryPayload;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\WebSocket\Response;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\ActionTestCase;

class MoveStoryTest extends ActionTestCase
{
    /**
     * @var ProjectRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectRepositoryMock;

    protected function initActionMocks()
    {
        parent::initActionMocks();

        $this->projectRepositoryMock = $this->createMock(ProjectRepository::class);
    }

    protected function getProcessorInstance(): ActionInterface
    {
        $processor = new MoveStory($this->storyRepositoryMock, $this->projectRepositoryMock);

        return $processor;
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'move-story';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return MoveStoryPayload::class;
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();

        $payload = new MoveStoryPayload();
        $payload->storyId = 'target-story-id';
        $payload->beforeStoryId = 'before-story-id';
        $payload->projectId = 'project-id';

        $project = $this->createMock(Project::class);
        $story = $this->createMock(Story::class);
        $positionStory = $this->createMock(Story::class);

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $this->storyRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['target-story-id'], ['before-story-id'])
            ->willReturnOnConsecutiveCalls(new Success($story), new Success($positionStory))
        ;

        $request = $this->makeRequest(33, 432, 'move-story', $payload);
        $response = $this->makeResponse($request);

        $this->storyRepositoryMock->expects($this->once())
            ->method('move')
            ->with($project, $story, $positionStory)
            ->willReturn(new Success(true))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 33, 432, 'story-moved');
    }

    public function testProcessFailed()
    {
        $processor = $this->getProcessorInstance();

        $payload = new MoveStoryPayload();
        $payload->storyId = 'target-story-id';
        $payload->beforeStoryId = 'before-story-id';
        $payload->projectId = 'project-id';

        $project = new Project('project-id', 'name');

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $this->storyRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['target-story-id'], ['before-story-id'])
            ->willReturnOnConsecutiveCalls(new Success(null), new Success(null))
        ;

        $request = $this->makeRequest(33, 432, 'move-story', $payload);
        $response = $this->makeResponse($request);

        $this->storyRepositoryMock->expects($this->never())
            ->method('move')
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 33, 432, 'error');
        $this->assertEquals(
            "Story id '{$payload->storyId}' do not moved after story id {$payload->beforeStoryId}",
            $response->getPayload()['message']
        );
    }
}
