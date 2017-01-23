<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\GetAll;

use Amp\Deferred as Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class GetStories extends AbstractAction
{
    /**
     * @var StoryRepository
     */
    protected $storyRepository;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    public function __construct(
        StoryRepository $storyRepository,
        ProjectRepository $projectRepository
    ) {
        $this->storyRepository = $storyRepository;
        $this->projectRepository = $projectRepository;
    }

    public function getSupportedType(): string
    {
        return 'get-stories';
    }

    public function getExpectedRequestPayload(): string
    {
        return ProjectIdPayload::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var ProjectIdPayload $payload */
        $payload = $request->getAttribute('payloadObject');
        $project = yield $this->projectRepository->get($payload->projectId);
        $stories = yield $this->storyRepository->getAllByProject($project);

        $response = $response->withType('stories');
        $storiesPayload = new StoriesPayload();
        $storiesPayload->stories = $stories;
        $response = $response->withPayload((array)$storiesPayload);

        $promisor->resolve($response);
    }
}
