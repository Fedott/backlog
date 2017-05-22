<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Story\Move;

use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Action\EmptyPayload;
use Fedot\Backlog\Action\ErrorPayload;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class MoveStory extends AbstractAction
{
    /**
     * @var StoryRepository
     */
    protected $storyRepository;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    public function __construct(StoryRepository $storyRepository, ProjectRepository $projectRepository)
    {
        $this->storyRepository = $storyRepository;
        $this->projectRepository = $projectRepository;
    }

    public function getSupportedType(): string
    {
        return 'move-story';
    }

    public function getExpectedRequestPayload(): string
    {
        return MoveStoryPayload::class;
    }

    protected function execute(RequestInterface $request, ResponseInterface $response)
    {
        /** @var MoveStoryPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        $project = yield $this->projectRepository->get($payload->projectId);
        $story = yield $this->storyRepository->get($payload->storyId);
        $positionStory = yield $this->storyRepository->get($payload->beforeStoryId);

        if ($project && $story && $positionStory) {
            yield $this->storyRepository->move(
                $project,
                $story,
                $positionStory
            );

            $response = $response->withType('story-moved');
            $response = $response->withPayload((array) new EmptyPayload());
        } else {
            $errorPayload = new ErrorPayload(
                "Story id '{$payload->storyId}' do not moved after story id {$payload->beforeStoryId}"
            );
            $response = $response->withType('error');
            $response = $response->withPayload((array)$errorPayload);
        }

        return $response;
    }
}
