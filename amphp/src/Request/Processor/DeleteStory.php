<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Deferred;
use Amp\Promise;
use Amp\Promisor;
use Fedot\Backlog\Payload\DeleteStoryPayload;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;

class DeleteStory extends AbstractProcessor
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

    protected function execute(Promisor $promisor, Request $request, Response $response)
    {
        /** @var DeleteStoryPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        $result = yield $this->storiesRepository->deleteByIds(
            $payload->projectId,
            $payload->storyId
        );

        if ($result) {
            $response = $response->withType('story-deleted');
        }

        $promisor->succeed($response);
    }
}
