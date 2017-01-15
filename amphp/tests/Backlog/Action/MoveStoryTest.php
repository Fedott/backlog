<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Action;

use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Story\Move\MoveStory;
use Fedot\Backlog\Action\Story\Move\MoveStoryPayload;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\ActionTestCase;

class MoveStoryTest extends ActionTestCase
{
    protected function getProcessorInstance(): ActionInterface
    {
        $processor = new MoveStory($this->storyRepositoryMock);

        return $processor;
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'move-story';
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();

        $payload = new MoveStoryPayload();
        $payload->storyId = 'target-story-id';
        $payload->beforeStoryId = 'before-story-id';
        $payload->projectId = 'project-id';

        $request = $this->makeRequest(33, 432, 'move-story', $payload);
        $response = $this->makeResponse($request);

        $this->storyRepositoryMock->expects($this->once())
            ->method('moveByIds')
            ->with('project-id', 'target-story-id', 'before-story-id')
            ->willReturn(new Success(true))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 33, 432, 'story-moved');
    }

    public function testProcessFailed()
    {
        $processor = $this->getProcessorInstance();

        $payload = new MoveStoryPayload();
        $payload->storyId = 'target-story-id';
        $payload->beforeStoryId = 'before-story-id';
        $payload->projectId = 'project-id';

        $request = $this->makeRequest(33, 432, 'move-story', $payload);
        $response = $this->makeResponse($request);

        $this->storyRepositoryMock->expects($this->once())
            ->method('moveByIds')
            ->with('project-id', 'target-story-id', 'before-story-id')
            ->willReturn(new Success(false))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 33, 432, 'error');
        $this->assertEquals(
            "Story id '{$payload->storyId}' do not moved after story id {$payload->beforeStoryId}",
            $response->getPayload()['message']
        );
    }
}
