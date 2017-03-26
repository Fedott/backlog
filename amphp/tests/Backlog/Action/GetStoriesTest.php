<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Action;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Story\GetAll\GetStories;
use Fedot\Backlog\Action\Story\GetAll\ProjectIdPayload;
use Fedot\Backlog\Action\Story\GetAll\StoriesPayload;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\WebSocket\Response;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\ActionTestCase;

class GetStoriesTest extends ActionTestCase
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
        return new GetStories(
            $this->storyRepositoryMock,
            $this->projectRepositoryMock,
            $this->normalizerMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'get-stories';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return ProjectIdPayload::class;
    }

    public function testProcess()
    {
        /** @var Story[]|PHPUnit_Framework_MockObject_MockObject[] $stories */
        $stories = [
            $this->createMock(Story::class),
            $this->createMock(Story::class),
            $this->createMock(Story::class),
        ];

        $stories[1]->expects($this->once())->method('isCompleted')->willReturn(true);
        $stories[0]->expects($this->once())->method('isCompleted')->willReturn(false);
        $stories[2]->expects($this->once())->method('isCompleted')->willReturn(false);

        $completedStory = $stories[1];

        $processor = $this->getProcessorInstance();

        $payload = new ProjectIdPayload();
        $payload->projectId = 'project-id';

        $project = $this->createMock(Project::class);

        $request = $this->makeRequest(34, 777, 'get-stories', $payload);
        $response = $this->makeResponse($request);

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $this->storyRepositoryMock->expects($this->once())
            ->method('getAllByProject')
            ->with($this->equalTo($project))
            ->willReturn(new Success($stories))
        ;

        $normalizedStories = ['stories' => [
            ['id' => 'test'],
            ['id' => 'test3'],
        ]];
        $this->normalizerMock->expects($this->once())
            ->method('normalize')
            ->with($this->callback(function (StoriesPayload $payload) use ($completedStory) {
                $this->assertCount(2, $payload->stories);

                foreach ($payload->stories as $story) {
                    $this->assertNotSame($completedStory, $story);
                }

                return true;
            }))
            ->willReturn($normalizedStories)
        ;

        /** @var Response $response */
        $response = \Amp\Promise\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 34, 777, 'stories');

        $this->assertEquals($normalizedStories, $response->getPayload());
    }
}
