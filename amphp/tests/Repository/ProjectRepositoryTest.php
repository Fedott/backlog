<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Repository;

use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;

class ProjectRepositoryTest extends BaseTestCase
{
    /**
     * @var ProjectRepository
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
        $this->repository = new ProjectRepository($this->redisClientMock, $this->serializerMock);
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
            ->method('set')
            ->with($this->equalTo('projects:entities:random-uuid'), $this->equalTo('json-string'))
            ->willReturn(new Success(true))
        ;

        $this->redisClientMock->expects($this->once())
            ->method('lPush')
            ->with($this->equalTo('projects:index:by-user:testUser'), $this->equalTo('projects:entities:random-uuid'))
            ->willReturn(new Success(true))
        ;

        $promise = $this->repository->create($user, $project);

        $result = \Amp\wait($promise);
        $this->assertEquals(true, $result);
    }
}
