<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Request\Processor\ProjectCreate;
use Fedot\Backlog\WebSocket\Response;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\Fedot\Backlog\BaseTestCase;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class ProjectCreateTest extends RequestProcessorTestCase
{
    /**
     * @var ProjectsRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectRepositoryMock;

    /**
     * @var UuidFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidFactoryMock;

    protected function initProcessorMocks()
    {
        parent::initProcessorMocks();

        $this->uuidFactoryMock = $this->createMock(UuidFactory::class);
        $this->projectRepositoryMock = $this->createMock(ProjectsRepository::class);
    }

    protected function getProcessorInstance(): ProcessorInterface
    {
        return new ProjectCreate($this->projectRepositoryMock,
            $this->uuidFactoryMock,
            $this->webSocketAuthServiceMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'create-project';
    }

    public function testProcess()
    {
        $payload = new Project();
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
            ->with($this->equalTo($user), $this->callback(function (Project $story) {
                $this->assertEquals('UUIDSuperUnique', $story->id);
                $this->assertEquals('first project', $story->name);

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        $processor = $this->getProcessorInstance();

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 33, 432, 'project-created');

        $this->assertEquals('UUIDSuperUnique', $response->getPayload()['id']);
        $this->assertEquals('first project', $response->getPayload()['name']);
    }
}
