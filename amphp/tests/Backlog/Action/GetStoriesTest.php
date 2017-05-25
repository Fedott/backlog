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
use Symfony\Component\Serializer\Serializer;
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
        global $container;

        return new GetStories(
            $this->storyRepositoryMock,
            $this->projectRepositoryMock,
            $container->get('serializer')
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
            new Story('id1', 'title1', 'text1', $this->createMock(Project::class), false),
            new Story('id2', 'title2', 'text2', $this->createMock(Project::class), true),
            new Story('id3', 'title3', 'text3', $this->createMock(Project::class), false),
        ];

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

        $expectedStoriesArray = ['stories' => [
            ['id' => 'id1', 'title' => 'title1', 'text' => 'text1', 'completed' => false, 'project' => ['id' => '', 'name' => '']],
            ['id' => 'id3', 'title' => 'title3', 'text' => 'text3', 'completed' => false, 'project' => ['id' => '', 'name' => '']],
        ]];

        /** @var Response $response */
        $response = \Amp\Promise\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 34, 777, 'stories');

        $this->assertEquals($expectedStoriesArray, $response->getPayload());
    }
}
