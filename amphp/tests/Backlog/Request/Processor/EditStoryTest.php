<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Processor\EditStory;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class EditStoryTest extends RequestProcessorTestCase
{
    protected function getProcessorInstance(): ProcessorInterface
    {
        $this->initProcessorMocks();

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

        $this->assertEquals(Story::class, $processor->getExpectedRequestPayload());
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();
        $storyRepositoryMock = $this->storyRepositoryMock;

        $payload = new Story();
        $payload->id = 'story-id';
        $payload->title = 'story title';
        $payload->text = 'story text';
        $request = new Request(33, 'edit-story', 432, (array)$payload);
        $request = $request->withAttribute('payloadObject', $payload);

        $response = new Response($request->getId(), $request->getClientId());

        $storyRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Story $story) {
                $this->assertEquals('story-id', $story->id);
                $this->assertEquals('story title', $story->title);
                $this->assertEquals('story text', $story->text);

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

        $payload = new Story();
        $payload->id = 'jgfjhfgj-erwer-dsfsd';
        $payload->title = 'story title';
        $payload->text = 'story text';
        $request = new Request(33, 'edit-story', 432, (array)$payload);
        $request = $request->withAttribute('payloadObject', $payload);

        $response = new Response($request->getId(), $request->getClientId());

        $storyRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Story $story) {
                $this->assertEquals('jgfjhfgj-erwer-dsfsd', $story->id);
                $this->assertEquals('story title', $story->title);
                $this->assertEquals('story text', $story->text);

                return true;
            }))
            ->willReturn(new Success(false))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals(33, $response->getRequestId());
        $this->assertEquals(432, $response->getClientId());
        $this->assertEquals('error', $response->getType());
        $this->assertEquals("Story id 'jgfjhfgj-erwer-dsfsd' do not saved", $response->getPayload()['message']);
    }
}
