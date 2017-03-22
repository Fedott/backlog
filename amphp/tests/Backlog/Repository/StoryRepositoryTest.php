<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Repository;

use function Amp\Promise\wait;
use Amp\Promise;
use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\DataMapper\IdentityMap;
use Fedot\DataMapper\Redis\FetchManager;
use Fedot\DataMapper\Redis\KeyGenerator;
use Fedot\DataMapper\Redis\ModelManager;
use Fedot\DataMapper\Redis\PersistManager;
use Fedot\DataMapper\Redis\RelationshipManager;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class StoryRepositoryTest extends BaseTestCase
{
    /**
     * @var ModelManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelManagerMock;

    /**
     * @return StoryRepository
     */
    protected function getRepositoryInstance()
    {
        $this->modelManagerMock = $this->createMock(ModelManager::class);

        return new StoryRepository($this->modelManagerMock);
    }

    public function testGetAllByProject()
    {
        $repository = $this->getRepositoryInstance();

        $stories = [
            $this->createMock(Story::class),
            $this->createMock(Story::class),
            $this->createMock(Story::class),
        ];

        $projectMock = $this->createMock(Project::class);

        $projectMock->expects($this->once())
            ->method('getStories')
            ->willReturn($stories)
        ;

        $resultPromise = $repository->getAllByProject($projectMock);
        $this->assertInstanceOf(Promise::class, $resultPromise);

        $result = \Amp\Promise\wait($resultPromise);

        $this->assertEquals($stories, $result);
    }

    public function testCreate()
    {
        $repository = $this->getRepositoryInstance();

        $storyMock = $this->createMock(Story::class);
        $project = $this->createMock(Project::class);

        $this->modelManagerMock->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive(
                [$storyMock, $this->isInstanceOf(IdentityMap::class)],
                [$project, $this->isInstanceOf(IdentityMap::class)]
            )
            ->willReturn(new Success(true))
        ;

        $resultPromise = $repository->create($project, $storyMock);
        $result = \Amp\Promise\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testDelete()
    {
        $project = $this->createMock(Project::class);
        $story = $this->createMock(Story::class);

        $repository = $this->getRepositoryInstance();

        $this->modelManagerMock->expects($this->once())
            ->method('remove')
            ->with($story)
            ->willReturn(new Success(true))
        ;

        $this->modelManagerMock->expects($this->once())
            ->method('persist')
            ->with($project)
            ->willReturn(new Success(true))
        ;

        $project->expects($this->once())
            ->method('removeStory')
            ->with($story)
        ;

        $result = wait($repository->delete($project, $story));
        $this->assertEquals(true, $result);
    }

    public function testSave()
    {
        $repository = $this->getRepositoryInstance();

        $story = $this->createMock(Story::class);

        $this->modelManagerMock->expects($this->once())
            ->method('persist')
            ->with($story)
            ->willReturn(new Success(true))
        ;

        $result = wait($repository->save($story));
        $this->assertTrue($result);
    }

    public function testMovePositive()
    {
        $repository = $this->getRepositoryInstance();

        $project = $this->createMock(Project::class);
        $story = $this->createMock(Story::class);
        $positionStory = $this->createMock(Story::class);

        $project->expects($this->once())
            ->method('moveStoryBeforeStory')
            ->with($story, $positionStory)
        ;

        $this->modelManagerMock->expects($this->exactly(1))
            ->method('persist')
            ->withConsecutive(
                [$project]
            )
            ->willReturn(new Success(true))
        ;

        $resultPromise = $repository->move($project, $story, $positionStory);
        $result = \Amp\Promise\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testGet()
    {
        $storyRepository = $this->getRepositoryInstance();

        $story = $this->createMock(Story::class);

        $this->modelManagerMock->expects($this->once())
            ->method('find')
            ->with(Story::class, 'story-id')
            ->willReturn(new Success($story))
        ;

        $result = wait($storyRepository->get('story-id'));

        $this->assertEquals($story, $result);
    }
}
