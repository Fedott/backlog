<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Story\Edit;

use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Action\ErrorPayload;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class EditStory extends AbstractAction
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
        return 'edit-story';
    }

    public function getExpectedRequestPayload(): string
    {
        return EditStoryPayload::class;
    }

    protected function execute(RequestInterface $request, ResponseInterface $response)
    {
        /** @var EditStoryPayload $payload */
        $payload = $request->getAttribute('payloadObject');

        /** @var Story $story */
        $story = yield $this->storyRepository->get($payload->id);

        if (null !== $story) {
            $story->edit($payload->title, $payload->text);

            yield $this->storyRepository->save($story);

            $response = $response->withType('story-edited');
            $response = $response->withPayload([
                'id' => $story->getId(),
                'title' => $story->getTitle(),
                'text' => $story->getText(),
            ]);
        } else {
            $response = $response->withType('error');
            $response = $response->withPayload((array) new ErrorPayload("Story id '{$payload->id}' do not saved"));
        }

        return $response;
    }
}
