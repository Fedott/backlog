<?php declare(strict_types=1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Payload\ProjectIdPayload;
use Fedot\Backlog\Payload\StoriesPayload;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;

class GetStories extends AbstractProcessor
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * GetStories constructor.
     *
     * @param StoriesRepository                        $storiesRepository
     */
    public function __construct(
        StoriesRepository $storiesRepository
    ){
        $this->storiesRepository = $storiesRepository;
    }

    /**
     * @inheritDoc
     */
    public function getSupportedType(): string
    {
        return 'get-stories';
    }

    /**
     * @inheritDoc
     */
    public function getExpectedRequestPayload(): string
    {
        return ProjectIdPayload::class;
    }

    protected function execute(Promisor $promisor, Request $request, Response $response)
    {
        /** @var ProjectIdPayload $payload */
        $payload = $request->getAttribute('payloadObject');
        $projectId = $payload->projectId;
        $stories = yield $this->storiesRepository->getAllByProjectId($projectId);

        $response = $response->withType('stories');
        $storiesPayload = new StoriesPayload();
        $storiesPayload->stories = $stories;
        $response = $response->withPayload((array)$storiesPayload);

        $promisor->succeed($response);
    }
}
