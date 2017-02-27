<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Requirement\GetAll;

use Amp\Deferred;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Repository\RequirementRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GetAllRequirementsAction extends AbstractAction
{
    /**
     * @var StoryRepository
     */
    private $storyRepository;

    /**
     * @var RequirementRepository
     */
    private $requirementRepository;

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    public function __construct(
        StoryRepository $storyRepository,
        RequirementRepository $requirementRepository,
        NormalizerInterface $normalizer
    ) {
        $this->storyRepository = $storyRepository;
        $this->requirementRepository = $requirementRepository;
        $this->normalizer = $normalizer;
    }

    protected function execute(Deferred $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var GetAllRequirementsPayload $payload */
        $payload = $request->getAttribute('payloadObject');
        $story = yield $this->storyRepository->get($payload->storyId);

        $requirements = yield $this->requirementRepository->getAllByStory($story);

        $response = $response->withType('requirements');
        $response = $response->withPayload(['requirements' => $this->normalizer->normalize($requirements)]);

        $promisor->resolve($response);
    }

    public function getSupportedType(): string
    {
        return 'story/requirements/getAll';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return GetAllRequirementsPayload::class;
    }
}
