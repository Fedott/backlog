<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\StoryIdPayload;
use Fedot\Backlog\Request\Processor\MarkStoryAsCompleted;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class MarkStoryAsCompletedTest extends RequestProcessorTestCase
{
    protected function getProcessorInstance(): ProcessorInterface
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
