<?php declare(strict_types = 1);

namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Payload\MoveStoryPayload;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Generator;

class MoveStory extends AbstractProcessor
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
     * @return string
     */
    public function getSupportedType(): string
    {
        return 'move-story';
    }

    public function getExpectedRequestPayload(): string
    {
        return MoveStoryPayload::class;
    }

    protected function execute(Promisor $promisor, Request $request, Response $response)
    {
        /** @var MoveStoryPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        $result = yield $this->storiesRepository->moveByIds(
            $payload->projectId,
            $payload->storyId,
            $payload->beforeStoryId
        );

        if ($result === true) {
            $response = $response->withType('story-moved');
            $response = $response->withPayload((array) new EmptyPayload());
        } else {
            $errorPayload = new ErrorPayload(
                "Story id '{$payload->storyId}' do not moved after story id {$payload->beforeStoryId}"
            );
            $response = $response->withType('error');
            $response = $response->withPayload((array)$errorPayload);
        }

        $promisor->succeed($response);
    }
}
