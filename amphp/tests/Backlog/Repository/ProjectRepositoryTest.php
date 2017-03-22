<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Repository;

use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\DataMapper\FetchManagerInterface;
use Fedot\DataMapper\IdentityMap;
use Fedot\DataMapper\PersistManagerInterface;
use Fedot\DataMapper\Redis\ModelManager;
use Fedot\DataMapper\RelationshipManagerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class ProjectRepositoryTest extends BaseTestCase
{
    /**
     * @var ModelManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelManagerMock;

    /**
     * @var ProjectRepository
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->initRepositoryWithMocks();
    }

    private function initRepositoryWithMocks()
    {
        $this->modelManagerMock = $this->createMock(ModelManager::class);

        $this->repository = new ProjectRepository(
            $this->modelManagerMock
        );
    }

    public function testCreate()
    {
        $userMock = $this->createMock(User::class);

        $project = new Project('project-id', 'project name');

        $this->modelManagerMock->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive(
                [$project, $this->isInstanceOf(IdentityMap::class)],
                [$userMock, $this->isInstanceOf(IdentityMap::class)]
            )
            ->willReturn(new Success(true))
        ;

        $userMock->expects($this->once())
            ->method('addProject')
            ->with($project)
        ;

        $promise = $this->repository->create($userMock, $project);

        $result = \Amp\Promise\wait($promise);
        $this->assertEquals(true, $result);
    }

    public function testGetAll()
    {
        $userMock = $this->createMock(User::class);

        $projects = [
            new Project('project-id', 'project name'),
            new Project('project-id2', 'project name 2'),
            new Project('project-id3', 'project name 3'),
        ];

        $userMock->expects($this->once())
            ->method('getProjects')
            ->willReturn($projects)
        ;

        $resultPromise = $this->repository->getAllByUser($userMock);

        $result = \Amp\Promise\wait($resultPromise);

        $this->assertCount(3, $result);
        array_map(
            function (Project $project) {
                $this->assertInstanceOf(Project::class, $project);
            },
            $result
        );
    }

    public function testGet()
    {
        $projectId = 'id1';
        $project = new Project('project-id', 'project name');

        $this->modelManagerMock->expects($this->once())
            ->method('find')
            ->with(Project::class, $projectId)
            ->willReturn(new Success($project))
        ;

        $resultPromise = $this->repository->get('id1');

        $actualProject = \Amp\Promise\wait($resultPromise);

        $this->assertEquals($project, $actualProject);
    }

    public function testAddUser()
    {
        $projectMock = $this->createMock(Project::class);
        $userMock = $this->createMock(User::class);

        $this->modelManagerMock->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive(
                [$projectMock, $this->isInstanceOf(IdentityMap::class)],
                [$userMock, $this->isInstanceOf(IdentityMap::class)]
            )
            ->willReturn(new Success(true))
        ;

        $projectMock->expects($this->once())
            ->method('share')
            ->with($userMock)
        ;

        $result = \Amp\Promise\wait($this->repository->addUser($projectMock, $userMock));

        $this->assertTrue($result);
    }
}
