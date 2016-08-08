<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\StoriesPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;

class GetStories implements ProcessorInterface
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    /**
     * GetStories constructor.
     *
     * @param StoriesRepository                        $storiesRepository
     * @param WebSocketConnectionAuthenticationService $webSocketConnectionAuthentication
     */
    public function __construct(
        StoriesRepository $storiesRepository,
        WebSocketConnectionAuthenticationService $webSocketConnectionAuthentication
    ){
        $this->storiesRepository = $storiesRepository;
        $this->webSocketAuthService = $webSocketConnectionAuthentication;
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
        return 'get-stories';
    }

    /**
     * @inheritDoc
     */
    public function getExpectedRequestPayload(): string
    {
        return EmptyPayload::class;
    }

    /**
     * @param Request $request
     *
     * @return Generator
     */
    public function process(Request $request): Generator
    {
        $authUser = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());
        $stories = yield $this->storiesRepository->getAll($authUser);

        $response = new Response();
        $response->requestId = $request->id;
        $response->type = 'stories';
        $response->payload = new StoriesPayload();
        $response->payload->stories = $stories;

        $request->getResponseSender()->sendResponse($response, $request->getClientId());
    }
}
