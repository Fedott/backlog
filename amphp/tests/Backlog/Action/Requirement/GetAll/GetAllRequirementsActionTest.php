<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Action\Requirement\GetAll;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Requirement\GetAll\GetAllRequirementsAction;
use Fedot\Backlog\Action\Requirement\GetAll\GetAllRequirementsPayload;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\RequirementRepository;
use Fedot\Backlog\WebSocket\Response;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tests\Fedot\Backlog\ActionTestCase;
use function Amp\wait;

class GetAllRequirementsActionTest extends ActionTestCase
{
    /**
     * @var RequirementRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected $requirementRepositoryMock;

    /**
     * @var NormalizerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $normalizerMock;

    protected function getProcessorInstance(): ActionInterface
    {
        $this->requirementRepositoryMock = $this->createMock(RequirementRepository::class);
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);

        return new GetAllRequirementsAction(
            $this->storyRepositoryMock,
            $this->requirementRepositoryMock,
            $this->normalizerMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'story/requirements/getAll';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return GetAllRequirementsPayload::class;
    }

    public function testProcess()
    {
        $story = $this->createMock(Story::class);
        $requirement1 = $this->createMock(Requirement::class);
        $requirement2 = $this->createMock(Requirement::class);
        $requirement3 = $this->createMock(Requirement::class);

        $this->storyRepositoryMock->expects($this->once())
            ->method('get')
            ->with('story-id')
            ->willReturn(new Success($story))
        ;

        $requirements = [
            $requirement1,
            $requirement2,
            $requirement3,
        ];
        $this->requirementRepositoryMock->expects($this->once())
            ->method('getAllByStory')
            ->with($story)
            ->willReturn(
                new Success(
                    $requirements
                )
            )
        ;

        $this->normalizerMock->expects($this->once())
            ->method('normalize')
            ->with($requirements)
            ->willReturn([
                ['id' => 'req1', 'text' => 'req text 1'],
                ['id' => 'req2', 'text' => 'req text 2'],
                ['id' => 'req3', 'text' => 'req text 3'],
            ])
        ;

        $request = $this->makeRequest(7, 777, 'story/requirement/getAll', new GetAllRequirementsPayload('story-id'));

        /** @var Response $response */
        $response = wait($this->action->process($request, $this->makeResponse($request)));

        $this->assertResponseBasic($response, 7, 777, 'requirements');

        $this->assertEquals([
            ['id' => 'req1', 'text' => 'req text 1'],
            ['id' => 'req2', 'text' => 'req text 2'],
            ['id' => 'req3', 'text' => 'req text 3'],
        ], $response->getPayload()['requirements']);
    }
}
