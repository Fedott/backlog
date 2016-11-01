<?php declare(strict_types=1);

namespace Fedot\Backlog\Request\Processor;

use Amp\Deferred;
use Amp\Promise;
use Amp\Promisor;
use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;

class EditStory extends AbstractProcessor
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    public function __construct(
        StoriesRepository $storiesRepository
    ){
        $this->storiesRepository = $storiesRepository;
    }

    public function getSupportedType(): string
    {
        return 'edit-story';
    }

    public function getExpectedRequestPayload(): string
    {
        return Story::class;
    }

    protected function execute(Promisor $promisor, Request $request, Response $response)
    {
        /** @var Story $story */
        $story = $request->getAttribute('payloadObject');

        $result = yield $this->storiesRepository->save($story);

        if ($result === true) {
            $response = $response->withType('story-edited');
            $response = $response->withPayload((array) $story);
        } else {
            $response = $response->withType('error');
            $response = $response->withPayload((array) new ErrorPayload("Story id '{$story->id}' do not saved"));
        }

        $promisor->succeed($response);
    }
}
