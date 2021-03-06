<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Action\Project;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\EmptyPayload;
use Fedot\Backlog\Action\Project\GetAll\ProjectsGetAll;
use Fedot\Backlog\Action\Project\GetAll\ProjectsPayload;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tests\Fedot\Backlog\ActionTestCase;

class GetProjectsTest extends ActionTestCase
{
    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

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
        $this->projectRepository = new ProjectRepository($this->modelManager);
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);

        return new ProjectsGetAll($this->projectRepository, $this->webSocketAuthServiceMock, $this->normalizerMock);
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'get-projects';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return EmptyPayload::class;
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();

        $payload = new EmptyPayload();

        $request = $this->makeRequest(66, 432, 'get-projects', $payload);
        $response = $this->makeResponse($request);

        $user = new User('testUser', 'hash');

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

        array_map(function (Project $project) use ($user) {
            $this->modelManager->persist($project);

            $user->addProject($project);
        }, $projects);

        $this->modelManager->persist($user);

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
        $response = \Amp\Promise\wait($processor->process($request, $response));

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
