<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\Create;

use Amp\Deferred as Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Action\ErrorPayload;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Ramsey\Uuid\UuidFactory;

class CreateStory extends AbstractAction
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
        return StoryCreatePayload::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var StoryCreatePayload $payload */
        $payload = $request->getAttribute('payloadObject');
        $projectId = $payload->projectId;
        $project = yield $this->projectRepository->get($projectId);
        $story = new Story(
            $this->uuidFactory->uuid4()->toString(),
            $payload->title,
            $payload->text,
            $project
        );

        yield $this->storyRepository->create($project, $story);

        $response = $response->withType('story-created');
        $response = $response->withPayload([
            'id' => $story->getId(),
            'title' => $story->getTitle(),
            'text' => $story->getText(),
        ]);

        $promisor->resolve($response);
    }
}
