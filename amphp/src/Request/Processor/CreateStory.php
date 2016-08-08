<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;
use Ramsey\Uuid\UuidFactory;

class CreateStory implements ProcessorInterface
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

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
     * @param UuidFactory $uuidFactory
     * @param WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
     */
    public function __construct(
        StoriesRepository $storiesRepository,
        UuidFactory $uuidFactory,
        WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
    ) {
        $this->storiesRepository = $storiesRepository;
        $this->uuidFactory = $uuidFactory;
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
        return Story::class;
    }

    /**
     * @param Request $request
     *
     * @return Generator
     */
    public function process(Request $request): Generator
    {
        /** @var Story $story */
        $story = $request->payload;
        $story->id = $this->uuidFactory->uuid4()->toString();

        $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());

        $result = yield $this->storiesRepository->create($user, $story);

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
    }
}
