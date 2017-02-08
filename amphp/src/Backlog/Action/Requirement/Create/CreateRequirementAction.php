<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Requirement\Create;

use Amp\Deferred as Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Action\ErrorPayload;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Repository\RequirementRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Ramsey\Uuid\UuidFactory;

class CreateRequirementAction extends AbstractAction
{
    /**
     * @var RequirementRepository
     */
    private $requirementRepository;

    /**
     * @var StoryRepository
     */
    private $storyRepository;

    /**
     * @var UuidFactory
     */
    private $uuidFactory;

    public function __construct(
        RequirementRepository $requirementRepository,
        StoryRepository $storyRepository,
        UuidFactory $uuidFactory
    ) {
        $this->requirementRepository = $requirementRepository;
        $this->storyRepository = $storyRepository;
        $this->uuidFactory = $uuidFactory;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var CreateRequirementPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        $story = yield $this->storyRepository->get($payload->storyId);

        if ($story === null) {
            $response = $response->withType('error');
            $response = $response
                ->withPayload((array)new ErrorPayload("Story '{$payload->storyId}' not found"))
            ;

            $promisor->resolve($response);
            return;
        }

        $uuid = $this->uuidFactory->uuid4()->toString();

        $requirement = new Requirement($uuid, $payload->text);
        yield $this->requirementRepository->create($story, $requirement);

        $response = $response->withType('requirement-created');
        $response = $response->withPayload([
            'id' => $requirement->getId(),
            'text' => $requirement->getText(),
            'isCompleted' => $requirement->isCompleted(),
        ]);

        $promisor->resolve($response);
        return;
    }

    public function getSupportedType(): string
    {
        return 'story/requirement/create';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return CreateRequirementPayload::class;
    }
}
