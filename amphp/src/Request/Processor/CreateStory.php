<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Payload\ErrorPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\StoriesRepository;
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
     * CreateStory constructor.
     *
     * @param StoriesRepository $storiesRepository
     * @param UuidFactory       $uuidFactory
     */
    public function __construct(
        StoriesRepository $storiesRepository,
        UuidFactory $uuidFactory
    ) {
        $this->storiesRepository = $storiesRepository;
        $this->uuidFactory = $uuidFactory;
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
            $story->id = $this->uuidFactory->uuid4();

            $result = yield $this->storiesRepository->save($story);

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
        });
    }
}
