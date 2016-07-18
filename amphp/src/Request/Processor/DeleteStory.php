<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Payload\DeleteStoryPayload;
use Fedot\Backlog\Response\Payload\EmptyPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\StoriesRepository;

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
        return $request->type == 'delete-story';
    }

    /**
     * @param Request $request
     */
    public function process(Request $request)
    {
        /** @var DeleteStoryPayload $request->payload */

        \Amp\immediately(function () use ($request) {
            $result = yield $this->storiesRepository->delete($request->payload->storyId);

            if ($result) {
                $response = new Response();
                $response->type = 'story-deleted';
                $response->requestId = $request->id;
                $response->payload = new EmptyPayload();

                $request->getResponseSender()->sendResponse($response, $request->getClientId());
            }
        });
    }
}
