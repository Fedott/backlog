<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Payload\StoryPayload;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Ramsey\Uuid\UuidFactory;

class CreateStory extends AbstractProcessor
{
    /**
     * @var StoryRepository
     */
    protected $storyRepository;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    /**
     * @var UuidFactory
     */
    protected $uuidFactory;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    public function __construct(
        StoryRepository $storyRepository,
        ProjectRepository $projectRepository,
        UuidFactory $uuidFactory,
        WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
    ) {
        $this->storyRepository = $storyRepository;
        $this->projectRepository = $projectRepository;
        $this->uuidFactory = $uuidFactory;
        $this->webSocketAuthService = $webSocketConnectionAuthenticationService;
    }

    public function getSupportedType(): string
    {
        return 'create-story';
    }

    public function getExpectedRequestPayload(): string
    {
        return StoryPayload::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var StoryPayload $payload */
        $payload = $request->getAttribute('payloadObject');
        $projectId = $payload->projectId;
        $project = yield $this->projectRepository->get($projectId);
        $story = $payload->story;
        $story->id = $this->uuidFactory->uuid4()->toString();

        $result = yield $this->storyRepository->create($project, $story);

        if ($result === true) {
            $response = $response->withType('story-created');
            $response = $response->withPayload((array) $story);
        } else {
            $response = $response->withType('error');
            $response = $response->withPayload((array) new ErrorPayload("Story id '{$story->id}' already exists"));
        }

        $promisor->succeed($response);
    }
}