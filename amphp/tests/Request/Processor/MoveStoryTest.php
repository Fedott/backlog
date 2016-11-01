<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Payload\MoveStoryPayload;
use Fedot\Backlog\Request\Processor\MoveStory;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class MoveStoryTest extends RequestProcessorTestCase
{
    protected function getProcessorInstance(): ProcessorInterface
    {
        $this->initProcessorMocks();

        $processor = new MoveStory($this->storiesRepositoryMock);

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

        $this->storiesRepositoryMock->expects($this->once())
            ->method('moveByIds')
            ->with('project-id', 'target-story-id', 'before-story-id')
            ->willReturn(new Success(true))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 33, 432, 'story-moved');
    }
}
