<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Payload\StoryPayload;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Ramsey\Uuid\UuidFactory;

class CreateStory extends AbstractProcessor
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * @var ProjectsRepository
     */
    protected $projectsRepository;

    /**
     * @var UuidFactory
     */
    protected $uuidFactory;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    /**
     * CreateStory constructor.
     *
     * @param StoriesRepository $storiesRepository
     * @param ProjectsRepository $projectsRepository
     * @param UuidFactory $uuidFactory
     * @param WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
     */
    public function __construct(
        StoriesRepository $storiesRepository,
        ProjectsRepository $projectsRepository,
        UuidFactory $uuidFactory,
        WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
    ) {
        $this->storiesRepository = $storiesRepository;
        $this->projectsRepository = $projectsRepository;
        $this->uuidFactory = $uuidFactory;
        $this->webSocketAuthService = $webSocketConnectionAuthenticationService;
    }
    /**
     * @inheritDoc
     */
    public function getSupportedType(): string
    {
        return 'create-story';
    }

    /**
     * @inheritDoc
     */
    public function getExpectedRequestPayload(): string
    {
        return StoryPayload::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var StoryPayload $payload */
        $payload = $request->getAttribute('payloadObject');
        $projectId = $payload->projectId;
        $project = yield $this->projectsRepository->get($projectId);
        $story = $payload->story;
        $story->id = $this->uuidFactory->uuid4()->toString();

        $result = yield $this->storiesRepository->create($project, $story);

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
