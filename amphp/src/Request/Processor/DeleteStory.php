<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\DeleteStoryPayload;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\StoriesRepository;
use Generator;

class DeleteStory implements ProcessorInterface
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * DeleteStory constructor.
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
     * @inheritDoc
     */
    public function getSupportedType(): string
    {
        return 'delete-story';
    }

    /**
     * @inheritDoc
     */
    public function getExpectedRequestPayload(): string
    {
        return DeleteStoryPayload::class;
    }

    /**
     * @param Request $request
     *
     * @return Generator
     */
    public function process(Request $request): Generator
    {
        /** @var DeleteStoryPayload $request->payload */
        $result = yield $this->storiesRepository->delete($request->payload->storyId);

        if ($result) {
            $response = new Response();
            $response->type = 'story-deleted';
            $response->requestId = $request->id;
            $response->payload = new EmptyPayload();

            $request->getResponseSender()->sendResponse($response, $request->getClientId());
        }
    }
}
