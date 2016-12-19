<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Repository;

use Amp\Promise;
use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\DataStorage\Redis\FetchManager;
use Fedot\DataStorage\Redis\RelationshipManager;
use Fedot\DataStorage\Redis\KeyGenerator;
use Fedot\DataStorage\Redis\PersistManager;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\StoryRepository;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class StoryRepositoryTest extends BaseTestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ProjectRepository
     */
    protected $projectRepositoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected $redisClientMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|SerializerInterface
     */
    protected $serializerMock;

    /**
     * @return StoryRepository
     */
    protected function getRepositoryInstance()
    {
        $this->redisClientMock = $this->createMock(Client::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $keyGenerator = new KeyGenerator();
        $indexManager = new RelationshipManager($keyGenerator, $this->redisClientMock);
        $persistManager = new PersistManager($keyGenerator, $this->redisClientMock, $this->serializerMock);
        $fetchManager = new FetchManager($keyGenerator, $this->redisClientMock, $this->serializerMock);
        $this->projectRepositoryMock = $this->createMock(ProjectRepository::class);

        $repository = new StoryRepository($fetchManager, $persistManager, $indexManager, $this->projectRepositoryMock);

        return $repository;
    }

    public function testGetAllByProject()
    {
        $repository = $this->getRepositoryInstance();

        $project = new Project();
        $project->id = 'project-id';

        $ids = [
            "story-id1",
            "story-id2",
            "story-id3",
        ];
        $keys = [
            "entity:fedot_backlog_model_story:story-id1",
            "entity:fedot_backlog_model_story:story-id2",
            "entity:fedot_backlog_model_story:story-id3",
        ];
        $this->redisClientMock->expects($this->once())
            ->method('lRange')
            ->with($this->equalTo("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story"), $this->equalTo(0), $this->equalTo(-1))
            ->willReturn(new Success($ids))
        ;
        $this->redisClientMock->expects($this->once())
            ->method('mGet')
            ->with($this->equalTo($keys))
            ->willReturn(new Success([
                "first",
                "second",
                "story 3",
            ]))
        ;
        $this->serializerMock->expects($this->exactly(3))
            ->method('deserialize')
            ->withConsecutive(
                ["first", Story::class, "json"],
                ["second", Story::class, "json"],
                ["story 3", Story::class, "json"]
            )
            ->willReturnOnConsecutiveCalls(
                new Story(),
                new Story(),
                new Story()
            )
        ;

        $resultPromise = $repository->getAllByProject($project);
        $this->assertInstanceOf(Promise::class, $resultPromise);

        $result = \Amp\wait($resultPromise);

        $this->assertCount(3, $result);
        array_map(function($story) {
            $this->assertInstanceOf(Story::class, $story);
        }, $result);
    }

    public function testGetAllByProjectId()
    {
        $repository = $this->getRepositoryInstance();

        $project = new Project();
        $project->id = 'project-id';

        $ids = [
            "story-id1",
            "story-id2",
            "story-id3",
        ];
        $keys = [
            "entity:fedot_backlog_model_story:story-id1",
            "entity:fedot_backlog_model_story:story-id2",
            "entity:fedot_backlog_model_story:story-id3",
        ];
        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;
        $this->redisClientMock->expects($this->once())
            ->method('lRange')
            ->with($this->equalTo("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story"), $this->equalTo(0), $this->equalTo(-1))
            ->willReturn(new Success($ids))
        ;
        $this->redisClientMock->expects($this->once())
            ->method('mGet')
            ->with($this->equalTo($keys))
            ->willReturn(new Success([
                "first",
                "second",
                "story 3",
            ]))
        ;
        $this->serializerMock->expects($this->exactly(3))
            ->method('deserialize')
            ->withConsecutive(
                ["first", Story::class, "json"],
                ["second", Story::class, "json"],
                ["story 3", Story::class, "json"]
            )
            ->willReturnOnConsecutiveCalls(
                new Story(),
                new Story(),
                new Story()
            )
        ;

        $resultPromise = $repository->getAllByProjectId($project->id);
        $this->assertInstanceOf(Promise::class, $resultPromise);

        $result = \Amp\wait($resultPromise);

        $this->assertCount(3, $result);
        array_map(function($story) {
            $this->assertInstanceOf(Story::class, $story);
        }, $result);
    }

    public function testGetAllEmpty()
    {
        $repository = $this->getRepositoryInstance();

        $project = new Project();
        $project->id = 'project-id';

        $keys = [];
        $this->redisClientMock->expects($this->once())
            ->method('lRange')
            ->with($this->equalTo("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story"), $this->equalTo(0), $this->equalTo(-1))
            ->willReturn(new Success($keys))
        ;
        $this->redisClientMock->expects($this->never())
            ->method('mGet')
        ;
        $this->serializerMock->expects($this->never())
            ->method('deserialize')
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

        $project = new Project();
        $project->id = 'project-id';

        $redisSetNXPromise = new Success(true);
        $redisLPushPromise = new Success(true);
        $this->redisClientMock->expects($this->once())
            ->method('setNx')
            ->with("entity:fedot_backlog_model_story:story-id", "{json-mock}")
            ->willReturn($redisSetNXPromise)
        ;

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($story, 'json')
            ->willReturn("{json-mock}")
        ;

        $this->redisClientMock->expects($this->once())
            ->method('lPush')
            ->with("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story", "story-id")
            ->willReturn($redisLPushPromise)
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

        $project = new Project();
        $project->id = 'project-id';

        $redisSetNXPromise = new Success(false);
        $this->redisClientMock->expects($this->once())
            ->method('setNx')
            ->with("entity:fedot_backlog_model_story:story-id", "{json-mock}")
            ->willReturn($redisSetNXPromise)
        ;

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($story, 'json')
            ->willReturn("{json-mock}")
        ;

        $this->redisClientMock->expects($this->never())
            ->method('lPush')
        ;

        $resultPromise = $repository->create($project, $story);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(false, $result);
    }

    public function testDelete()
    {
        $project = new Project();
        $project->id = 'project-id';

        $story = new Story();
        $story->id = 'story-id';

        $repository = $this->getRepositoryInstance();

        $redisDelPromise = new Success(1);
        $redisLRemPromise = new Success(true);
        $this->redisClientMock->expects($this->once())
            ->method('del')
            ->with('entity:fedot_backlog_model_story:story-id')
            ->willReturn($redisDelPromise)
        ;

        $this->redisClientMock->expects($this->once())
            ->method('lRem')
            ->with(
                "index:fedot_backlog_model_project:project-id:fedot_backlog_model_story",
                "entity:fedot_backlog_model_story:story-id",
                0
            )
            ->willReturn($redisLRemPromise)
        ;

        $resultPromise = $repository->delete($project, $story);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testDeleteByIds()
    {
        $project = new Project();
        $project->id = 'project-id';

        $story = new Story();
        $story->id = 'story-id';

        $repository = $this->getRepositoryInstance();

        $this->projectRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $this->redisClientMock
            ->expects($this->once())
            ->method('get')
            ->with('entity:fedot_backlog_model_story:story-id')
            ->willReturn(new Success('{story-json}'))
        ;

        $this->serializerMock->expects($this->once())
            ->method('deserialize')
            ->with('{story-json}', Story::class, "json")
            ->willReturn($story)
        ;

        $redisDelPromise = new Success(1);
        $redisLRemPromise = new Success(true);
        $this->redisClientMock->expects($this->once())
            ->method('del')
            ->with('entity:fedot_backlog_model_story:story-id')
            ->willReturn($redisDelPromise)
        ;

        $this->redisClientMock->expects($this->once())
            ->method('lRem')
            ->with(
                "index:fedot_backlog_model_project:project-id:fedot_backlog_model_story",
                "entity:fedot_backlog_model_story:story-id",
                0
            )
            ->willReturn($redisLRemPromise)
        ;

        $resultPromise = $repository->deleteByIds($project->id, $story->id);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testSave()
    {
        $repository = $this->getRepositoryInstance();

        $story = new Story();
        $story->id = 'story-id';

        $redisPromise = new Success(true);
        $this->redisClientMock->expects($this->once())
            ->method('set')
            ->with('entity:fedot_backlog_model_story:story-id', '{json-mock}')
            ->willReturn($redisPromise)
        ;

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($story, 'json')
            ->willReturn('{json-mock}')
        ;

        $resultPromise = $repository->save($story);
        $this->assertEquals($redisPromise, $resultPromise);
    }

    public function testMovePositive()
    {
        $repository = $this->getRepositoryInstance();

        $project = new Project();
        $project->id = 'project-id';

        $story = new Story();
        $story->id = 'story-id';

        $positionStory = new Story();
        $positionStory->id = 'story-id2';

        $this->redisClientMock->expects($this->once())
            ->method('lRem')
            ->with("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story", 'story-id', 0)
            ->willReturn(new Success(1))
        ;

        $this->redisClientMock->expects($this->once())
            ->method('lInsert')
            ->with("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story", 'before', "story-id2", "story-id")
            ->willReturn(new Success(3))
        ;

        $resultPromise = $repository->move($project, $story, $positionStory);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testMoveByIds()
    {
        $repository = $this->getRepositoryInstance();

        $project = new Project();
        $project->id = 'project-id';

        $story = new Story();
        $story->id = 'story-id';

        $positionStory = new Story();
        $positionStory->id = 'story-id2';

        $this->redisClientMock->expects($this->once())
            ->method('lRem')
            ->with("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story", 'story-id', 0)
            ->willReturn(new Success(1))
        ;

        $this->redisClientMock->expects($this->once())
            ->method('lInsert')
            ->with("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story", 'before', "story-id2", "story-id")
            ->willReturn(new Success(3))
        ;

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with($project->id)
            ->willReturn(new Success($project))
        ;

        $this->redisClientMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['entity:fedot_backlog_model_story:story-id'],
                ['entity:fedot_backlog_model_story:story-id2']
            )
            ->willReturnOnConsecutiveCalls(
                new Success('story-json-1'),
                new Success('story-json-2')
            )
        ;

        $this->serializerMock->expects($this->exactly(2))
            ->method('deserialize')
            ->withConsecutive(
                ['story-json-1', Story::class, 'json'],
                ['story-json-2', Story::class, 'json']
            )
            ->willReturnOnConsecutiveCalls(
                $story,
                $positionStory
            )
        ;

        $resultPromise = $repository->moveByIds($project->id, $story->id, $positionStory->id);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }
}
