<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Action;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Story\Delete\DeleteStory;
use Fedot\Backlog\Action\Story\Delete\DeleteStoryPayload;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\ActionTestCase;

class DeleteStoryTest extends ActionTestCase
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
        $processor = new DeleteStory($this->storyRepositoryMock, $this->projectRepositoryMock);

        return $processor;
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'delete-story';
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();

        $deleteStoryPayload = new DeleteStoryPayload();
        $deleteStoryPayload->storyId = 'story-id';
        $deleteStoryPayload->projectId = 'project-id';

        $project = new Project('project-id', 'name');
        $story = new Story();

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $this->storyRepositoryMock->expects($this->once())
            ->method('get')
            ->with('story-id')
            ->willReturn(new Success($story))
        ;

        $this->storyRepositoryMock->expects($this->once())
            ->method('delete')
            ->with(
                $project, $story
            )
            ->willReturn(new Success(true))
        ;

        $request = new Request(34, 'delete-story', 777, [
            'storyId' => 'story-id',
            'projectId' => 'project-id',
        ]);
        $request = $request->withAttribute('payloadObject', $deleteStoryPayload);
        $response = new Response($request->getId(), $request->getClientId());

        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals('story-deleted', $response->getType());
        $this->assertEquals([], $response->getPayload());
    }
}
