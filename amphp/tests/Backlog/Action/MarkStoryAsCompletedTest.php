<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Action;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Story\MarkAsCompleted\MarkStoryAsCompleted;
use Fedot\Backlog\Action\Story\MarkAsCompleted\StoryIdPayload;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\ActionTestCase;

class MarkStoryAsCompletedTest extends ActionTestCase
{
    protected function getProcessorInstance(): ActionInterface
    {
        $processor = new MarkStoryAsCompleted($this->storyRepositoryMock);

        return $processor;
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'story-mark-as-completed';
    }

    public function testProcess()
    {
        $story = new Story();

        $this->storyRepositoryMock->expects($this->once())
            ->method('get')
            ->with('story-id')
            ->willReturn(new Success($story))
        ;

        $this->storyRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Story $storyForSave) use ($story) {
                $this->assertEquals($story, $storyForSave);
                $this->assertEquals(true, $storyForSave->isCompleted);

                return true;
            }))
            ->willReturn(new Success(true))
        ;

        $payload = new StoryIdPayload();
        $payload->storyId = 'story-id';
        $request = $this->makeRequest(3, 166, 'story-mark-as-completed', $payload);
        $response = $this->makeResponse($request);

        /** @var Response $response */
        $response = \Amp\wait($this->getProcessorInstance()->process($request, $response));

        $this->assertResponseBasic($response, 3, 166, 'story-marked-as-completed');
    }

    public function testProcessNotFoundStory()
    {
        $story = new Story();

        $this->storyRepositoryMock->expects($this->once())
            ->method('get')
            ->with('story-id')
            ->willReturn(new Success(null))
        ;

        $this->storyRepositoryMock->expects($this->never())->method('save');

        $payload = new StoryIdPayload();
        $payload->storyId = 'story-id';
        $request = $this->makeRequest(3, 166, 'story-mark-as-completed', $payload);
        $response = $this->makeResponse($request);

        /** @var Response $response */
        $response = \Amp\wait($this->getProcessorInstance()->process($request, $response));

        $this->assertResponseBasic($response, 3, 166, 'error');
    }
}