<?php declare(strict_types = 1);

namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Payload\MoveStoryPayload;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Repository\StoriesRepository;
use Generator;

class MoveStory implements ProcessorInterface
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * MoveStory constructor.
     *
     * @param StoriesRepository $storiesRepository
     */
    public function __construct(StoriesRepository $storiesRepository)
    {
        $this->storiesRepository = $storiesRepository;
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
        return 'move-story';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return MoveStoryPayload::class;
    }

    /**
     * @param Request $request
     *
     * @return Generator
     */
    public function process(Request $request): Generator
    {
        /** @var MoveStoryPayload $payload */
        $payload = $request->payload;

        $result = yield $this->storiesRepository->move($payload->storyId, $payload->beforeStoryId);

        $response = new Response();
        $response->requestId = $request->id;

        if ($result === true) {
            $response->type = 'story-moved';
            $response->payload = new EmptyPayload();
        } else {
            $response->type = 'error';
            $response->payload = new ErrorPayload();
            $response->payload->message
                = "Story id '{$payload->storyId}' do not moved after story id {$payload->beforeStoryId}";
        }

        $request->getResponseSender()->sendResponse($response, $request->getClientId());
    }
}
