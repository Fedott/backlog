<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Action\Project;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Project\Create\CreateProjectPayload;
use Fedot\Backlog\Action\Project\Create\ProjectCreate;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\WebSocket\Response;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\Fedot\Backlog\ActionTestCase;

class ProjectCreateTest extends ActionTestCase
{
    /**
     * @var ProjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectRepositoryMock;

    /**
     * @var UuidFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidFactoryMock;

    protected function initActionMocks()
    {
        parent::initActionMocks();

        $this->uuidFactoryMock = $this->createMock(UuidFactory::class);
        $this->projectRepositoryMock = $this->createMock(ProjectRepository::class);
    }

    protected function getProcessorInstance(): ActionInterface
    {
        return new ProjectCreate(
            $this->projectRepositoryMock,
            $this->uuidFactoryMock,
            $this->webSocketAuthServiceMock,
            $this->normalizerMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'create-project';
    }

    public function testProcess()
    {
        $payload = new CreateProjectPayload();
        $payload->name = 'first project';

        $request = $this->makeRequest(33, 432, 'create-project', $payload);
        $response = $this->makeResponse($request);

        $user = new User();

        $this->webSocketAuthServiceMock
            ->expects($this->once())
            ->method('getAuthorizedUserForClient')
            ->with($this->equalTo(432))
            ->willReturn($user)
        ;

        $uuidMock = $this->createMock(Uuid::class);
        $this->uuidFactoryMock
            ->expects($this->once())
            ->method('uuid4')
            ->willReturn($uuidMock)
        ;

        $uuidMock->expects($this->once())
            ->method('toString')
            ->willReturn('UUIDSuperUnique')
        ;

        $this->projectRepositoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($user), $this->callback(function (Project $project) {
                $this->assertEquals('UUIDSuperUnique', $project->getId());
                $this->assertEquals('first project', $project->getName());

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        $this->normalizerMock->expects($this->once())
            ->method('normalize')
            ->with($this->callback(function (Project $project) {
                $this->assertEquals('UUIDSuperUnique', $project->getId());
                $this->assertEquals('first project', $project->getName());

                return true;
            }))
            ->willReturn([
                'id' => 'UUIDSuperUnique',
                'name' => 'first project',
            ])
        ;

        $processor = $this->getProcessorInstance();

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 33, 432, 'project-created');

        $this->assertEquals('UUIDSuperUnique', $response->getPayload()['id']);
        $this->assertEquals('first project', $response->getPayload()['name']);
    }
}
