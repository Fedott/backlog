<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Action\Requirement\Create;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Requirement\Create\CreateRequirementAction;
use Fedot\Backlog\Action\Requirement\Create\CreateRequirementPayload;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\RequirementRepository;
use PHPUnit_Framework_MockObject_MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\Fedot\Backlog\ActionTestCase;
use function Amp\wait;

class CreateRequirementActionTest extends ActionTestCase
{
    /**
     * @var UuidFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $uuidFactoryMock;

    /**
     * @var RequirementRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $requirementRepositoryMock;

    protected function getProcessorInstance(): ActionInterface
    {
        $this->uuidFactoryMock = $this->createMock(UuidFactory::class);
        $this->requirementRepositoryMock = $this->createMock(RequirementRepository::class);

        return new CreateRequirementAction(
            $this->requirementRepositoryMock,
            $this->storyRepositoryMock,
            $this->uuidFactoryMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'story/requirements/create';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return CreateRequirementPayload::class;
    }

    public function testProcessPositive()
    {
        $payload = new CreateRequirementPayload();
        $payload->text = 'requirement-text';
        $payload->storyId = 'story-id';
        $request = $this->makeRequest(7, 777, 'story/requirement/create', $payload);

        $story = $this->createMock(Story::class);

        $this->storyRepositoryMock->expects($this->once())
            ->method('get')
            ->with('story-id')
            ->willReturn(new Success($story));

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

        $this->requirementRepositoryMock->expects($this->once())
            ->method('create')
            ->with($story, $this->callback(function (Requirement $requirement) {
                $this->assertEquals('UUIDSuperUnique', $requirement->getId());
                $this->assertEquals('requirement-text', $requirement->getText());

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        $response = wait($this->action->process($request, $this->makeResponse($request)));

        $this->assertResponseBasic($response, 7, 777, 'requirement-created');
        $actualPayload = $response->getPayload();
        $this->assertEquals([
            'id' => 'UUIDSuperUnique',
            'text' => 'requirement-text',
            'completed' => false,
        ], $actualPayload);
    }

    public function testProcessNotFoundStory()
    {
        $payload = new CreateRequirementPayload();
        $payload->text = 'requirement-text';
        $payload->storyId = 'story-id';
        $request = $this->makeRequest(7, 777, 'story/requirement/create', $payload);

        $this->storyRepositoryMock->expects($this->once())
            ->method('get')
            ->with('story-id')
            ->willReturn(new Success(null));

        $response = wait($this->action->process($request, $this->makeResponse($request)));

        $this->assertResponseError($response, 7, 777, "Story 'story-id' not found");
    }
}
