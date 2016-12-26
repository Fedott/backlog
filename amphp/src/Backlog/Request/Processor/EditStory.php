<?php declare(strict_types=1);

namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\ErrorPayload;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class EditStory extends AbstractProcessor
{
    /**
     * @var StoryRepository
     */
    protected $storyRepository;

    public function __construct(
        StoryRepository $storyRepository
    ){
        $this->storyRepository = $storyRepository;
    }

    public function getSupportedType(): string
    {
        return 'edit-story';
    }

    public function getExpectedRequestPayload(): string
    {
        return Story::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var Story $story */
        $story = $request->getAttribute('payloadObject');

        $result = yield $this->storyRepository->save($story);

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
