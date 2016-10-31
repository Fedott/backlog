<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Deferred;
use Amp\Promise;
use Fedot\Backlog\Payload\DeleteStoryPayload;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;

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
        return $request->getType() === $this->getSupportedType();
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
     * @param Response $response
     *
     * @return Promise
     */
    public function process(Request $request, Response $response): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $request, $response) {
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
        });

        return $promisor->promise();
    }
}
