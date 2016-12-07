<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Request\Processor\GetProjects;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class GetProjectsTest extends RequestProcessorTestCase
{
    /**
     * @var ProjectRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectRepositoryMock;

    /**
     * @var WebSocketConnectionAuthenticationService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $webAuthMock;

    protected function getProcessorInstance(): ProcessorInterface
    {
        $this->initProcessorMocks();

        $this->projectRepositoryMock = $this->createMock(ProjectRepository::class);

        return new GetProjects($this->projectRepositoryMock, $this->webSocketAuthServiceMock);
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'get-projects';
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();

        $payload = new EmptyPayload();

        $request = $this->makeRequest(66, 432, 'get-projects', $payload);
        $response = $this->makeResponse($request);

        $user = new User();

        $this->webSocketAuthServiceMock
            ->expects($this->once())
            ->method('getAuthorizedUserForClient')
            ->with($this->equalTo(432))
            ->willReturn($user)
        ;

        $projects = [
            new Project(),
            new Project(),
            new Project(),
        ];

        $this->projectRepositoryMock->expects($this->once())
            ->method('getAllByUser')
            ->with($user)
            ->willReturn(new Success($projects))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 66, 432, 'projects');

        $this->assertEquals((array) $projects, $response->getPayload()['projects']);
    }
}
