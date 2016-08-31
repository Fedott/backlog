<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Request\Processor\ProjectCreate;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\Fedot\Backlog\BaseTestCase;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class ProjectCreateTest extends RequestProcessorTestCase
{
    /**
     * @var ProjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectRepositoryMock;

    /**
     * @var UuidFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidFactoryMock;

    /**
     * @var ProjectCreate
     */
    protected $processor;

    protected function initProcessorMocks()
    {
        parent::initProcessorMocks();

        $this->uuidFactoryMock = $this->createMock(UuidFactory::class);
        $this->projectRepositoryMock = $this->createMock(ProjectRepository::class);

        $this->processor = new ProjectCreate($this->projectRepositoryMock,
            $this->uuidFactoryMock,
            $this->webSocketAuthServiceMock
        );
    }



    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $actualResult = $this->processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request();
        $request1->type = 'create-project';

        $request2 = new Request();
        $request2->type = 'get-stories';

        $request3 = new Request();

        return [
            'ping type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    public function testProcess()
    {
        $request = new Request();
        $request->id = 33;
        $request->type = 'create-project';
        $request->payload = new Project();
        $request->payload->name = 'first project';
        $request->setClientId(432);
        $request->setResponseSender($this->responseSenderMock);

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

        $this->responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function (Response $response){
                $this->assertEquals(33, $response->requestId);
                $this->assertEquals('project-created', $response->type);

                /** @var Project $responsePayload */
                $responsePayload = $response->payload;
                $this->assertInstanceOf(Project::class, $responsePayload);
                $this->assertEquals('UUIDSuperUnique', $responsePayload->id);
                $this->assertEquals('first project', $responsePayload->name);

                return true;
            }), $this->equalTo(432))
        ;

        $this->startProcessMethod($this->processor, $request);
    }
}
