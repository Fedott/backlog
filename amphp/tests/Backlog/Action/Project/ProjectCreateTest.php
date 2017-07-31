<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Action\Project;

use function Amp\Promise\wait;
use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Project\Create\ProjectCreate;
use Fedot\Backlog\Action\Project\Create\ProjectCreatePayload;
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
     * @var ProjectRepository
     */
    protected $projectRepository;

    /**
     * @var UuidFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidFactoryMock;

    protected function initActionMocks()
    {
        parent::initActionMocks();

        $this->uuidFactoryMock = $this->createMock(UuidFactory::class);
        $this->projectRepository = new ProjectRepository($this->modelManager);
    }

    protected function getProcessorInstance(): ActionInterface
    {
        return new ProjectCreate(
            $this->projectRepository,
            $this->uuidFactoryMock,
            $this->webSocketAuthServiceMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'create-project';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return ProjectCreatePayload::class;
    }

    public function testProcess()
    {
        $payload = new ProjectCreatePayload();
        $payload->name = 'first project';

        $request = $this->makeRequest(33, 432, 'create-project', $payload);
        $response = $this->makeResponse($request);

        $user = new User('testUser', 'hash');

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

        $processor = $this->getProcessorInstance();

        /** @var Response $response */
        $response = \Amp\Promise\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 33, 432, 'project-created');

        $this->assertEquals('UUIDSuperUnique', $response->getPayload()['id']);
        $this->assertEquals('first project', $response->getPayload()['name']);

        /** @var Project $actualProject */
        $actualProject = wait($this->projectRepository->get('UUIDSuperUnique'));
        $this->assertEquals('first project', $actualProject->getName());
        $this->assertEquals('UUIDSuperUnique', $actualProject->getId());
    }
}
