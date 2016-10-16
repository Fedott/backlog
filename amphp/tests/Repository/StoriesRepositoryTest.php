<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Repository;

use Amp\Promise;
use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Redis\FetchManager;
use Fedot\Backlog\Redis\IndexManager;
use Fedot\Backlog\Redis\KeyGenerator;
use Fedot\Backlog\Redis\PersistManager;
use Fedot\Backlog\Repository\StoriesRepository;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class StoriesRepositoryTest extends BaseTestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected $redisClientMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|SerializerInterface
     */
    protected $serializerMock;

    /**
     * @return StoriesRepository
     */
    protected function getRepositoryInstance()
    {
        $this->redisClientMock = $this->createMock(Client::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $keyGenerator = new KeyGenerator();
        $indexManager = new IndexManager($keyGenerator, $this->redisClientMock);
        $persistManager = new PersistManager($keyGenerator, $this->redisClientMock, $this->serializerMock);
        $fetchManager = new FetchManager($keyGenerator, $this->redisClientMock, $this->serializerMock);

        $repository = new StoriesRepository($fetchManager, $persistManager, $indexManager);

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
            ->with("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story", 'entity:fedot_backlog_model_story:story-id', 0)
            ->willReturn(new Success(1))
        ;

        $this->redisClientMock->expects($this->once())
            ->method('lInsert')
            ->with("index:fedot_backlog_model_project:project-id:fedot_backlog_model_story", 'before', "entity:fedot_backlog_model_story:story-id2", "entity:fedot_backlog_model_story:story-id")
            ->willReturn(new Success(3))
        ;

        $resultPromise = $repository->move($project, $story, $positionStory);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testMoveNegativeRemove()
    {
        $this->markTestSkipped("Temporary");
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $redisClientMock->expects($this->once())
            ->method('lRem')
            ->with("stories:sort:default", 'story:storyId333', 1)
            ->willReturn(new Success(0))
        ;

        $redisClientMock->expects($this->once())
            ->method('lInsert')
            ->with("stories:sort:default", 'before', "story:storyId888", "story:storyId333")
            ->willReturn(new Success(3))
        ;

        $resultPromise = $repository->move('storyId333', 'storyId888');
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testMoveNegativeInsert()
    {
        $this->markTestSkipped("Temporary");
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $redisClientMock->expects($this->once())
            ->method('lRem')
            ->with("stories:sort:default", 'story:storyId333', 1)
            ->willReturn(new Success(1))
        ;

        $redisClientMock->expects($this->once())
            ->method('lInsert')
            ->with("stories:sort:default", 'before', "story:storyId888", "story:storyId333")
            ->willReturn(new Success(-1))
        ;

        $redisClientMock->expects($this->once())
            ->method('lPush')
            ->with('stories:sort:default', 'story:storyId333')
            ->willReturn(new Success(1))
        ;

        $resultPromise = $repository->move('storyId333', 'storyId888');
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(false, $result);
    }
}
