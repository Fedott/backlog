<?php

namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\StoriesRepository;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

class EditStory implements ProcessorInterface
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
     * EditStory constructor.
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
     * @return string
     */
    public function getSupportedType(): string
    {
        return 'edit-story';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return Story::class;
    }

    /**
     * @param Request $request
     */
    public function process(Request $request)
    {
        \Amp\immediately(function () use ($request) {
            /** @var Story $story */
            $story = $request->payload;

            $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());

            $result = yield $this->storiesRepository->save($user, $story);

            $response            = new Response();
            $response->requestId = $request->id;

            if ($result === true) {
                $response->type    = 'story-edited';
                $response->payload = $story;
            } else {
                $response->type             = 'error';
                $response->payload          = new ErrorPayload();
                $response->payload->message = "Story id '{$story->id}' do not saved";
            }

            $request->getResponseSender()->sendResponse($response, $request->getClientId());
        });
    }
}
