<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Action\Requirement\ChangeCompleted;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Requirement\ChangeCompleted\RequirementChangeCompletedAction;
use Fedot\Backlog\Action\Requirement\ChangeCompleted\RequirementChangeCompletedPayload;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Repository\RequirementRepository;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\ActionTestCase;
use function Amp\wait;

class RequirementChangeCompletedActionTest extends ActionTestCase
{
    /**
     * @var RequirementRepository|PHPUnit_Framework_MockObject_MockObject
     */
    protected $requirementRepositoryMock;

    protected function getProcessorInstance(): ActionInterface
    {
        $this->requirementRepositoryMock = $this->createMock(RequirementRepository::class);

        return new RequirementChangeCompletedAction($this->requirementRepositoryMock);
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'story/requirements/change-completed';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return RequirementChangeCompletedPayload::class;
    }

    /**
     * @dataProvider providerTestProcess
     */
    public function testProcess(
        bool $completed,
        bool $isComplete,
        string $expectedMethod,
        bool $expectedCall,
        bool $founded,
        string $expectedResponseType
    ) {
        $requirementMock = $this->createMock(Requirement::class);

        $requirementMock->method('isCompleted')
            ->willReturn($isComplete)
        ;
        if ($founded && $expectedCall) {
            $requirementMock->expects($this->once())
                ->method($expectedMethod)
            ;
            $this->requirementRepositoryMock->expects($this->once())
                ->method('save')
                ->with($requirementMock)
                ->willReturn(new Success(true))
            ;
        } else {
            $requirementMock->expects($this->never())->method('complete');
            $requirementMock->expects($this->never())->method('incomplete');
        }

        $this->requirementRepositoryMock->expects($this->once())
            ->method('get')
            ->with('req-id')
            ->willReturn(new Success($founded ? $requirementMock : null))
        ;

        $request = $this->makeRequest(
            7,
            123,
            'story/requirements/change-completed',
            new RequirementChangeCompletedPayload('req-id', $completed)
        );

        $response = wait($this->action->process($request, $this->makeResponse($request)));

        $this->assertResponseBasic($response, 7, 123, $expectedResponseType);
    }

    public function providerTestProcess()
    {
        return [
            'complete found success' => [true, false, 'complete', true, true, 'success'],
            'complete found error' => [true, true, 'complete', false, true, 'error'],
            'complete not found error' => [true, false, 'complete', false, false, 'error'],
            'incomplete found success' => [false, true, 'incomplete', true, true, 'success'],
            'incomplete found error' => [false, false, 'incomplete', false, true, 'error'],
            'incomplete not found error' => [false, true, 'incomplete', false, false, 'error'],
        ];
    }
}
