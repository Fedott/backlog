<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\GetAll;

use Amp\Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class GetStories extends AbstractAction
{
    /**
     * @var StoryRepository
     */
    protected $storyRepository;

    public function __construct(
        StoryRepository $storyRepository
    ) {
        $this->storyRepository = $storyRepository;
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
        $projectId = $payload->projectId;
        $stories = yield $this->storyRepository->getAllByProjectId($projectId);

        $response = $response->withType('stories');
        $storiesPayload = new StoriesPayload();
        $storiesPayload->stories = $stories;
        $response = $response->withPayload((array)$storiesPayload);

        $promisor->succeed($response);
    }
}
