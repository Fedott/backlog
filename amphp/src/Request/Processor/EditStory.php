<?php declare(strict_types=1);

namespace Fedot\Backlog\Request\Processor;

use Amp\Deferred;
use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;

class EditStory implements ProcessorInterface
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * EditStory constructor.
     *
     * @param StoriesRepository                        $storiesRepository
     */
    public function __construct(
        StoriesRepository $storiesRepository
    ){
        $this->storiesRepository = $storiesRepository;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool
    {
        return $request->getType() === $this->getSupportedType();
    }

    /**
     * @return string
     */
    public function getSupportedType(): string
    {
        return 'edit-story';
    }

    /**
     * @inheritdoc
     */
    public function getExpectedRequestPayload(): string
    {
        return Story::class;
    }

    /**
     * @inheritdoc
     */
    public function process(Request $request, Response $response): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $request, $response) {
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
        });

        return $promisor->promise();
    }
}
