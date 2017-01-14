<?php declare(strict_types = 1);

namespace Fedot\Backlog\Action;

use Amp\Promisor;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Payload\MoveStoryPayload;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class MoveStory extends AbstractAction
{
    /**
     * @var StoryRepository
     */
    protected $storyRepository;

    public function __construct(StoryRepository $storyRepository)
    {
        $this->storyRepository = $storyRepository;
    }

    public function getSupportedType(): string
    {
        return 'move-story';
    }

    public function getExpectedRequestPayload(): string
    {
        return MoveStoryPayload::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var MoveStoryPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        $result = yield $this->storyRepository->moveByIds(
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
