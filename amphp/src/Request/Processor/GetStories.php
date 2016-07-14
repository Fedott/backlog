<?php
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Payload\StoriesPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\StoriesRepository;

class GetStories implements ProcessorInterface
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * GetStories constructor.
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
        return $request->type === 'get-stories';
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function process(Request $request)
    {
        \Amp\immediately(function () use ($request) {
            $stories = yield $this->storiesRepository->getAll();

            $response = new Response();
            $response->requestId = $request->id;
            $response->type = 'stories';
            $response->payload = new StoriesPayload();
            $response->payload->stories = $stories;

            $request->getResponseSender()->sendResponse($response, $request->getClientId());
        });
    }
}
