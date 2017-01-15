<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Action\Project;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Project\Get\GetProjects;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\ProjectsPayload;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tests\Fedot\Backlog\ActionTestCase;

class GetProjectsTest extends ActionTestCase
{
    /**
     * @var ProjectRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectRepositoryMock;

    /**
     * @var WebSocketConnectionAuthenticationService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $webAuthMock;

    /**
     * @var NormalizerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $normalizerMock;

    protected function getProcessorInstance(): ActionInterface
    {
        $this->projectRepositoryMock = $this->createMock(ProjectRepository::class);
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);

        return new GetProjects($this->projectRepositoryMock, $this->webSocketAuthServiceMock, $this->normalizerMock);
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
            new Project('project-id', 'project name 1'),
            new Project('project-id2', 'project name 2'),
            new Project('project-id3', 'project name 3'),
        ];

        $this->projectRepositoryMock->expects($this->once())
            ->method('getAllByUser')
            ->with($user)
            ->willReturn(new Success($projects))
        ;

        $this->normalizerMock->expects($this->once())
            ->method('normalize')
            ->with($this->callback(function (ProjectsPayload $payload) use ($projects) {
                $this->assertEquals($projects, $payload->projects);

                return true;
            }))
            ->willReturn(
                [
                    'projects' => [
                        ['id' => 'project-id', 'name' => 'project name 1'],
                        ['id' => 'project-id2', 'name' => 'project name 2'],
                        ['id' => 'project-id3', 'name' => 'project name 3'],
                    ],
                ]
            )
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 66, 432, 'projects');

        $this->assertEquals(
            [
                ['id' => 'project-id', 'name' => 'project name 1'],
                ['id' => 'project-id2', 'name' => 'project name 2'],
                ['id' => 'project-id3', 'name' => 'project name 3'],
            ],
            $response->getPayload()['projects']
        );
    }
}
