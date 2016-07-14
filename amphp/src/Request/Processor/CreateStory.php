<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Payload\ErrorPayload;
use Fedot\Backlog\Response\Payload\StoryPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\StoriesRepository;

class CreateStory implements ProcessorInterface
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * CreateStory constructor.
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
        return $request->type === 'create-story';
    }

    /**
     * @param Request $request
     */
    public function process(Request $request)
    {
        \Amp\immediately(function () use ($request) {
            /** @var Story $story */
            $story = $request->payload;
            $result = yield $this->storiesRepository->save($story);

            var_dump($result);

            $response            = new Response();
            $response->requestId = $request->id;

            if ($result === true) {
                $response->type    = 'story-created';
                $response->payload = $story;
            } else {
                $response->type             = 'error';
                $response->payload          = new ErrorPayload();
                $response->payload->message = "Story number {$story->number} already exists";
            }

            $request->getResponseSender()->sendResponse($response, $request->getClientId());
        });
    }
}
