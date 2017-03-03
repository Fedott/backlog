<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Repository;

use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\DataMapper\FetchManagerInterface;
use Fedot\DataMapper\PersistManagerInterface;
use Fedot\DataMapper\RelationshipManagerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class ProjectRepositoryTest extends BaseTestCase
{
    /**
     * @var RelationshipManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationshipManagerInterfaceMock;

    /**
     * @var PersistManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistManagerInterfaceMock;

    /**
     * @var FetchManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchManagerInterfaceMock;

    /**
     * @var ProjectRepository
     */
    protected $repository;

    /**
     * @var Client|PHPUnit_Framework_MockObject_MockObject
     */
    protected $redisClientMock;

    /**
     * @var SerializerInterface|PHPUnit_Framework_MockObject_MockObject
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

        $this->relationshipManagerInterfaceMock = $this->createMock(RelationshipManagerInterface::class);
        $this->persistManagerInterfaceMock = $this->createMock(PersistManagerInterface::class);
        $this->fetchManagerInterfaceMock = $this->createMock(FetchManagerInterface::class);

        $this->repository = new ProjectRepository(
            $this->relationshipManagerInterfaceMock,
            $this->persistManagerInterfaceMock,
            $this->fetchManagerInterfaceMock
        );
    }

    public function testCreate()
    {
        $user = new User();
        $user->username = 'testUser';

        $project = new Project('project-id', 'project name');

        $this->persistManagerInterfaceMock->expects($this->once())
            ->method('persist')
            ->with($project, false)
            ->willReturn(new Success(true))
        ;

        $this->relationshipManagerInterfaceMock->expects($this->once())
            ->method('addManyToMany')
            ->with($user, $project)
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

        $projects = [
            new Project('project-id', 'project name'),
            new Project('project-id2', 'project name 2'),
            new Project('project-id3', 'project name 3'),
        ];

        $this->relationshipManagerInterfaceMock->expects($this->once())
            ->method('getIdsManyToMany')
            ->with($user, Project::class)
            ->willReturn(new Success($projectIds))
        ;

        $this->fetchManagerInterfaceMock->expects($this->once())
            ->method('fetchCollectionByIds')
            ->with(Project::class, $projectIds)
            ->willReturn(new Success($projects))
        ;

        $resultPromise = $this->repository->getAllByUser($user);

        $result = \Amp\wait($resultPromise);

        $this->assertCount(3, $result);
        array_map(
            function ($project) {
                $this->assertInstanceOf(Project::class, $project);
            },
            $result
        );
    }

    public function testGet()
    {
        $projectId = 'id1';
        $project = new Project('project-id', 'project name');

        $this->fetchManagerInterfaceMock->expects($this->once())
            ->method('fetchById')
            ->with(Project::class, $projectId)
            ->willReturn(new Success($project))
        ;

        $resultPromise = $this->repository->get('id1');

        $actualProject = \Amp\wait($resultPromise);

        $this->assertEquals($project, $actualProject);
    }

    public function testAddUser()
    {
        $project = new Project('project-id', 'project name');
        $user = new User();

        $this->relationshipManagerInterfaceMock->expects($this->once())
            ->method('addManyToMany')
            ->with($project, $user)
            ->willReturn(new Success(true))
        ;

        $result = \Amp\wait($this->repository->addUser($project, $user));

        $this->assertTrue($result);
    }
}
