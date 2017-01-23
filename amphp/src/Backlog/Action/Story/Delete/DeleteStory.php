<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\Delete;

use Amp\Deferred as Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class DeleteStory extends AbstractAction
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

        $project = yield $this->projectRepository->get($payload->projectId);
        $story = yield $this->storyRepository->get($payload->storyId);

        $result = yield $this->storyRepository->delete(
            $project,
            $story
        );

        if ($result) {
            $response = $response->withType('story-deleted');
        }

        $promisor->resolve($response);
    }
}
