<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Repository;

use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Infrastructure\Redis\FetchManager;
use Fedot\Backlog\Infrastructure\Redis\IndexManager;
use Fedot\Backlog\Infrastructure\Redis\KeyGenerator;
use Fedot\Backlog\Infrastructure\Redis\PersistManager;
use Fedot\Backlog\Repository\ProjectsRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class ProjectRepositoryTest extends BaseTestCase
{
    /**
     * @var ProjectsRepository
     */
    protected $repository;

    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redisClientMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializerMock;

    protected function setUp()
    {
        parent::setUp();

        $this->initRepositoryWithMocks();
    }

    private function initRepositoryWithMocks()
    {
        $this->redisClientMock = $this->createMock(Client::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $keyGenerator = new KeyGenerator();
        $indexManager = new IndexManager($keyGenerator, $this->redisClientMock);
        $persistManager = new PersistManager($keyGenerator, $this->redisClientMock, $this->serializerMock);
        $fetchManager = new FetchManager($keyGenerator, $this->redisClientMock, $this->serializerMock);

        $this->repository = new ProjectsRepository($indexManager, $persistManager, $fetchManager);
    }

    public function testCreate()
    {
        $user = new User();
        $user->username = 'testUser';

        $project = new Project();
        $project->id = 'random-uuid';
        $project->name = "First project";

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($project))
            ->willReturn('json-string')
        ;

        $this->redisClientMock->expects($this->once())
            ->method('setNx')
            ->with($this->equalTo('entity:fedot_backlog_model_project:random-uuid'), $this->equalTo('json-string'))
            ->willReturn(new Success(true))
        ;

        $this->redisClientMock->expects($this->once())
            ->method('lPush')
            ->with($this->equalTo('index:fedot_backlog_model_user:testUser:fedot_backlog_model_project'), $this->equalTo('random-uuid'))
            ->willReturn(new Success(true))
        ;

        $promise = $this->repository->create($user, $project);

        $result = \Amp\wait($promise);
        $this->assertEquals(true, $result);
    }

    public function testGetAll()
    {
        $user = new User();
        $user->username = 'testUser';

        $projectIds = [
            'id1',
            'id2',
            'id3',
        ];

        $projectKeys = [
            'entity:fedot_backlog_model_project:id1',
            'entity:fedot_backlog_model_project:id2',
            'entity:fedot_backlog_model_project:id3',
        ];

        $this->redisClientMock
            ->expects($this->once())
            ->method('lRange')
            ->with($this->equalTo("index:fedot_backlog_model_user:testUser:fedot_backlog_model_project"), $this->equalTo(0), $this->equalTo(-1))
            ->willReturn(new Success($projectIds))
        ;

        $this->redisClientMock->expects($this->once())
            ->method('mGet')
            ->with($this->equalTo($projectKeys))
            ->willReturn(new Success([
                "project-json-1",
                "project-json-2",
                "project-json-3",
            ]))
        ;

        $this->serializerMock
            ->expects($this->exactly(3))
            ->method('deserialize')
            ->withConsecutive(
                ["project-json-1", Project::class, "json"],
                ["project-json-2", Project::class, "json"],
                ["project-json-3", Project::class, "json"]
            )
            ->willReturnOnConsecutiveCalls(
                new Project(),
                new Project(),
                new Project()
            )
        ;

        $resultPromise = $this->repository->getAllByUser($user);

        $result = \Amp\wait($resultPromise);

        $this->assertCount(3, $result);
        array_map(function($project) {
            $this->assertInstanceOf(Project::class, $project);
        }, $result);
    }

    public function testGet()
    {
        $projectKey = 'entity:fedot_backlog_model_project:id1';

        $this->redisClientMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo($projectKey))
            ->willReturn(new Success("project-json-1"))
        ;

        $project = new Project();
        $this->serializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->with("project-json-1", Project::class, "json")
            ->willReturn($project)
        ;

        $resultPromise = $this->repository->get('id1');

        $actualProject = \Amp\wait($resultPromise);

        $this->assertEquals($project, $actualProject);
    }
}
