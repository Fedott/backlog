<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Action;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Story\Edit\EditStory;
use Fedot\Backlog\Action\Story\Edit\EditStoryPayload;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\ActionTestCase;

class EditStoryTest extends ActionTestCase
{
    protected function getProcessorInstance(): ActionInterface
    {
        return new EditStory(
            $this->storyRepositoryMock
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'edit-story';
    }

    public function testGetExpectedRequestPayload()
    {
        $processor = $this->getProcessorInstance();

        $this->assertEquals(EditStoryPayload::class, $processor->getExpectedRequestPayload());
    }

    protected function getExpectedPayloadType(): ?string
    {
        return EditStoryPayload::class;
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();
        $storyRepositoryMock = $this->storyRepositoryMock;

        $payload = new EditStoryPayload();
        $payload->id = 'story-id';
        $payload->title = 'story title';
        $payload->text = 'story text';
        $request = new Request(33, 'edit-story', 432, (array)$payload);
        $request = $request->withAttribute('payloadObject', $payload);

        $response = new Response($request->getId(), $request->getClientId());

        $story = new Story('story-id', 'empty', 'text', $this->createMock(Project::class));

        $storyRepositoryMock->expects($this->once())
            ->method('get')
            ->with('story-id')
            ->willReturn(new Success($story))
        ;

        $storyRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Story $story) {
                $this->assertEquals('story-id', $story->getId());
                $this->assertEquals('story title', $story->getTitle());
                $this->assertEquals('story text', $story->getText());

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals(33, $response->getRequestId());
        $this->assertEquals(432, $response->getClientId());
        $this->assertEquals('story-edited', $response->getType());

        $responsePayload = $response->getPayload();
        $this->assertEquals('story-id', $responsePayload['id']);
        $this->assertEquals('story title', $responsePayload['title']);
        $this->assertEquals('story text', $responsePayload['text']);
    }

    public function testProcessWithError()
    {
        $processor = $this->getProcessorInstance();
        $storyRepositoryMock = $this->storyRepositoryMock;

        $payload = new EditStoryPayload();
        $payload->id = 'jgfjhfgj-erwer-dsfsd';
        $payload->title = 'story title';
        $payload->text = 'story text';
        $request = new Request(33, 'edit-story', 432, (array)$payload);
        $request = $request->withAttribute('payloadObject', $payload);

        $response = new Response($request->getId(), $request->getClientId());

        $storyRepositoryMock->expects($this->once())
            ->method('get')
            ->with('jgfjhfgj-erwer-dsfsd')
            ->willReturn(new Success(null))
        ;

        $storyRepositoryMock->expects($this->never())
            ->method('save')
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals(33, $response->getRequestId());
        $this->assertEquals(432, $response->getClientId());
        $this->assertEquals('error', $response->getType());
        $this->assertEquals("Story id 'jgfjhfgj-erwer-dsfsd' do not saved", $response->getPayload()['message']);
    }
}
