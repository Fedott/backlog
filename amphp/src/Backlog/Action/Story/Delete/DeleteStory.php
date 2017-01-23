<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\Delete;

use Amp\Deferred as Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class DeleteStory extends AbstractAction
{
    /**
     * @var StoryRepository
     */
    protected $storyRepository;

    public function __construct(StoryRepository $storyRepository)
    {
        $this->storyRepository = $storyRepository;
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

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var DeleteStoryPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        $result = yield $this->storyRepository->deleteByIds(
            $payload->projectId,
            $payload->storyId
        );

        if ($result) {
            $response = $response->withType('story-deleted');
        }

        $promisor->resolve($response);
    }
}
