<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\StoryPayload;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;
use Ramsey\Uuid\UuidFactory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class CreateStory implements ProcessorInterface
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
     * @var Serializer
     */
    protected $serializer;

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
     * @param Serializer $serializer
     * @param WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
     */
    public function __construct(
        StoriesRepository $storiesRepository,
        ProjectsRepository $projectsRepository,
        UuidFactory $uuidFactory,
        Serializer $serializer,
        WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
    ) {
        $this->storiesRepository = $storiesRepository;
        $this->projectsRepository = $projectsRepository;
        $this->uuidFactory = $uuidFactory;
        $this->serializer = $serializer;
        $this->webSocketAuthService = $webSocketConnectionAuthenticationService;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool
    {
        return $request->type === $this->getSupportedType();
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

    /**
     * @param Request $request
     *
     * @return Generator
     */
    public function process(Request $request): Generator
    {
        /** @var StoryPayload $payload */
        $payload = $request->payload;
        $projectId = $payload->projectId;
        $project = yield $this->projectsRepository->get($projectId);
        $story = $this->serializer->denormalize($payload->story, Story::class);
        $story->id = $this->uuidFactory->uuid4()->toString();

        $result = yield $this->storiesRepository->create($project, $story);

        $response            = new Response();
        $response->requestId = $request->id;

        if ($result === true) {
            $response->type    = 'story-created';
            $response->payload = $story;
        } else {
            $response->type             = 'error';
            $response->payload          = new ErrorPayload();
            $response->payload->message = "Story id '{$story->id}' already exists";
        }

        $request->getResponseSender()->sendResponse($response, $request->getClientId());

        yield;
    }
}
