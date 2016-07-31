<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\StoriesPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\StoriesRepository;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

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
     * @return bool
     */
    public function process(Request $request)
    {
        \Amp\immediately(function () use ($request) {
            $authUser = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());
            $stories = yield $this->storiesRepository->getAll($authUser);

            $response = new Response();
            $response->requestId = $request->id;
            $response->type = 'stories';
            $response->payload = new StoriesPayload();
            $response->payload->stories = $stories;

            $request->getResponseSender()->sendResponse($response, $request->getClientId());
        });
    }
}
