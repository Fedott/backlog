<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Payload\StoryIdPayload;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class MarkStoryAsCompleted extends AbstractProcessor
{
    /**
     * @var StoriesRepository
     */
    protected $storyRepository;

    public function __construct(StoriesRepository $storyRepository)
    {
        $this->storyRepository = $storyRepository;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var StoryIdPayload $payload */
        $payload = $request->getAttribute('payloadObject');
        /** @var Story $story */
        $story = yield $this->storyRepository->get($payload->storyId);

        if ($story) {
            $story->isCompleted = true;
            $this->storyRepository->save($story);

            $response = $response->withType('story-marked-as-completed');
        } else {
            $response = $response->withType('error');
            $errorPayload = new ErrorPayload('Story not found');
            $response = $response->withPayload((array) $errorPayload);
        }

        $promisor->succeed($response);
    }

    public function getSupportedType(): string
    {
        return 'story-mark-as-completed';
    }

    public function getExpectedRequestPayload(): string
    {
        return StoryIdPayload::class;
    }
}
