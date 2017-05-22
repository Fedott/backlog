<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\MarkAsCompleted;

use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Action\ErrorPayload;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class MarkStoryAsCompleted extends AbstractAction
{
    /**
     * @var StoryRepository
     */
    protected $storyRepository;

    public function __construct(StoryRepository $storyRepository)
    {
        $this->storyRepository = $storyRepository;
    }

    protected function execute(RequestInterface $request, ResponseInterface $response)
    {
        /** @var StoryIdPayload $payload */
        $payload = $request->getAttribute('payloadObject');
        /** @var Story $story */
        $story = yield $this->storyRepository->get($payload->storyId);

        if ($story) {
            $story->complete();
            $this->storyRepository->save($story);

            $response = $response->withType('story-marked-as-completed');
        } else {
            $response = $response->withType('error');
            $errorPayload = new ErrorPayload('Story not found');
            $response = $response->withPayload((array) $errorPayload);
        }

        return $response;
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
