<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Requirement\Save;

use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Repository\RequirementRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class SaveAction extends AbstractAction
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
        /** @var SavePayload $payload */
        $payload = $request->getAttribute('payloadObject');

        /** @var Requirement $requirement */
        $requirement = yield $this->requirementRepository->get($payload->id);

        $requirement->edit($payload->text);

        yield $this->requirementRepository->save($requirement);

        $response = $response->withType('requirement-saved');
        $response = $response->withPayload([
            'id' => $requirement->getId(),
            'text' => $requirement->getText(),
            'completed' => $requirement->isCompleted(),
        ]);

        return $response;
    }

    public function getSupportedType(): string
    {
        return 'story/requirements/save';
    }

    public function getExpectedRequestPayload(): string
    {
        return SavePayload::class;
    }
}
