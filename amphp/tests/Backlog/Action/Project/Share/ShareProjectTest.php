<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Action\Project\Share;

use Amp\Success;
use Fedot\Backlog\Action\Project\Share\ProjectSharePayload;
use Fedot\Backlog\Action\Project\Share\ShareProject;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\UserRepository;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class ShareProjectTest extends RequestProcessorTestCase
{
    /**
     * @var UserRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected $userRepositoryMock;

    /**
     * @var ProjectRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectRepositoryMock;

    protected function initProcessorMocks()
    {
        parent::initProcessorMocks();

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->projectRepositoryMock = $this->createMock(ProjectRepository::class);
    }

    protected function getProcessorInstance(): ProcessorInterface
    {
        return new ShareProject(
            $this->userRepositoryMock,
            $this->projectRepositoryMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'project/share';
    }

    public function testSharePositive()
    {
        $payload = new ProjectSharePayload();
        $payload->projectId = 'project-id';
        $payload->userId = 'user-id';

        $project = new Project('project-id', 'project 1');
        $user = new User();

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $this->userRepositoryMock->expects($this->once())
            ->method('get')
            ->with('user-id')
            ->willReturn(new Success($user))
        ;

        $this->projectRepositoryMock->expects($this->once())
            ->method('addUser')
            ->with($project, $user)
            ->willReturn(new Success(true))
        ;

        $request = $this->makeRequest(1, 2, 'project/share', $payload);
        $response = $this->makeResponse($request);

        $response = \Amp\wait($this->processor->process($request, $response));

        $this->assertResponseBasic($response, 1, 2, 'success');
    }

    public function testShareUserNotFound()
    {
        $payload = new ProjectSharePayload();
        $payload->projectId = 'project-id';
        $payload->userId = 'user-id';

        $project = new Project('project-id', 'project 1');

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success($project))
        ;

        $this->userRepositoryMock->expects($this->once())
            ->method('get')
            ->with('user-id')
            ->willReturn(new Success(null))
        ;

        $this->projectRepositoryMock->expects($this->never())
            ->method('addUser')
        ;

        $request = $this->makeRequest(1, 2, 'project/share', $payload);
        $response = $this->makeResponse($request);

        $response = \Amp\wait($this->processor->process($request, $response));

        $this->assertResponseError($response, 1, 2, 'User or Project not found');
    }

    public function testShareProjectNotFound()
    {
        $payload = new ProjectSharePayload();
        $payload->projectId = 'project-id';
        $payload->userId = 'user-id';

        $user = new User();

        $this->projectRepositoryMock->expects($this->once())
            ->method('get')
            ->with('project-id')
            ->willReturn(new Success(null))
        ;

        $this->userRepositoryMock->expects($this->once())
            ->method('get')
            ->with('user-id')
            ->willReturn(new Success($user))
        ;

        $this->projectRepositoryMock->expects($this->never())
            ->method('addUser')
        ;

        $request = $this->makeRequest(1, 2, 'project/share', $payload);
        $response = $this->makeResponse($request);

        $response = \Amp\wait($this->processor->process($request, $response));

        $this->assertResponseError($response, 1, 2, 'User or Project not found');
    }
}
