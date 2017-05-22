<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Requirement\ChangeCompleted;

use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Repository\RequirementRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class RequirementChangeCompletedAction extends AbstractAction
{
    /**
     * @var RequirementRepository
     */
    private $requirementRepository;

    public function __construct(RequirementRepository $requirementRepository)
    {
        $this->requirementRepository = $requirementRepository;
    }

    protected function execute(RequestInterface $request, ResponseInterface $response)
    {
        /** @var RequirementChangeCompletedPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        /** @var Requirement $requirement */
        $requirement = yield $this->requirementRepository->get($payload->requirementId);

        if (null !== $requirement) {
            if ($payload->completed === true) {
                if (!$requirement->isCompleted()) {
                    $requirement->complete();
                    yield $this->requirementRepository->save($requirement);

                    return $response->withType('success');
                }
            } elseif ($payload->completed === false) {
                if ($requirement->isCompleted()) {
                    $requirement->incomplete();
                    yield $this->requirementRepository->save($requirement);

                    return $response->withType('success');
                }
            }
        }

        return $response->withType('error');
    }

    public function getSupportedType(): string
    {
        return 'story/requirements/change-completed';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return RequirementChangeCompletedPayload::class;
    }
}
