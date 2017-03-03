<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Repository;

use function Amp\wait;
use AsyncInterop\Promise;
use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\DataMapper\Redis\FetchManager;
use Fedot\DataMapper\Redis\KeyGenerator;
use Fedot\DataMapper\Redis\PersistManager;
use Fedot\DataMapper\Redis\RelationshipManager;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class StoryRepositoryTest extends BaseTestCase
{
    /**
     * @var RelationshipManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexManager;

    /**
     * @var PersistManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistManager;

    /**
     * @var FetchManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ProjectRepository
     */
    protected $projectRepositoryMock;

    /**
     * @return StoryRepository
     */
    protected function getRepositoryInstance()
    {
        $this->indexManager = $this->createMock(RelationshipManager::class);
        $this->persistManager = $this->createMock(PersistManager::class);
        $this->fetchManager = $this->createMock(FetchManager::class);
        $this->projectRepositoryMock = $this->createMock(ProjectRepository::class);

        $repository = new StoryRepository($this->fetchManager, $this->persistManager, $this->indexManager, $this->projectRepositoryMock);

        return $repository;
    }

    public function testGetAllByProject()
    {
        $repository = $this->getRepositoryInstance();

        $project = new Project('project-id', 'project name');

        $keys = [
            "entity:fedot_backlog_model_story:story-id1",
            "entity:fedot_backlog_model_story:story-id2",
            "entity:fedot_backlog_model_story:story-id3",
        ];

        $this->indexManager->expects($this->once())
            ->method('getIdsOneToMany')
            ->with($project, Story::class)
            ->willReturn(new Success($keys))
        ;

        $stories = [
            new Story(),
            new Story(),
            new Story()
        ];

        $this->fetchManager->expects($this->once())
            ->method('fetchCollectionByIds')
            ->with(Story::class, $keys)
            ->willReturn(new Success($stories))
        ;

        $resultPromise = $repository->getAllByProject($project);
        $this->assertInstanceOf(Promise::class, $resultPromise);

        $result = \Amp\wait($resultPromise);

        $this->assertEquals($stories, $result);
    }

    public function testGetAllEmpty()
    {
        $repository = $this->getRepositoryInstance();

        $project = new Project('project-id', 'project name');

        $keys = [];
        $this->indexManager->expects($this->once())
            ->method('getIdsOneToMany')
            ->with($project, Story::class)
            ->willReturn(new Success($keys))
        ;

        $this->fetchManager->expects($this->never())
            ->method($this->anything())
        ;

        $resultPromise = $repository->getAllByProject($project);
        $this->assertInstanceOf(Promise::class, $resultPromise);

        $result = \Amp\wait($resultPromise);

        $this->assertCount(0, $result);
        $this->assertEquals([], $result);
    }

    public function testCreate()
    {
        $repository = $this->getRepositoryInstance();

        $story = new Story();
        $story->id = 'story-id';

        $project = new Project('project-id', 'project name');

        $this->persistManager->expects($this->once())
            ->method('persist')
            ->with($story)
            ->willReturn(new Success(true))
        ;

        $this->indexManager->expects($this->once())
            ->method('addOneToMany')
            ->with($project, $story)
            ->willReturn(new Success(true))
        ;

        $resultPromise = $repository->create($project, $story);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testCreateErrorAlreadyExist()
    {
        $repository = $this->getRepositoryInstance();

        $story = new Story();
        $story->id = 'story-id';

        $project = new Project('project-id', 'project name');

        $this->persistManager->expects($this->once())
            ->method('persist')
            ->with($story)
            ->willReturn(new Success(false))
        ;

        $this->indexManager->expects($this->never())
            ->method($this->anything())
        ;

        $resultPromise = $repository->create($project, $story);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(false, $result);
    }

    public function testDelete()
    {
        $project = new Project('project-id', 'project name');

        $story = new Story();
        $story->id = 'story-id';

        $repository = $this->getRepositoryInstance();

        $this->persistManager->expects($this->once())
            ->method('remove')
            ->with($story)
            ->willReturn(new Success(true))
        ;

        $this->indexManager->expects($this->once())
            ->method('removeOneToMany')
            ->with($project, $story)
            ->willReturn(new Success(true))
        ;

        $resultPromise = $repository->delete($project, $story);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testSave()
    {
        $repository = $this->getRepositoryInstance();

        $story = new Story();
        $story->id = 'story-id';

        $this->persistManager->expects($this->once())
            ->method('persist')
            ->with($story, true)
            ->willReturn(new Success(true))
        ;

        $result = wait($repository->save($story));
        $this->assertTrue($result);
    }

    public function testMovePositive()
    {
        $repository = $this->getRepositoryInstance();

        $project = new Project('project-id', 'project name');

        $story = new Story();
        $story->id = 'story-id';

        $positionStory = new Story();
        $positionStory->id = 'story-id2';

        $this->indexManager->expects($this->once())
            ->method('moveValueOnOneToMany')
            ->with($project, $story, $positionStory)
            ->willReturn(new Success(true))
        ;

        $resultPromise = $repository->move($project, $story, $positionStory);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }
}
