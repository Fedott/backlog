<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Request\Processor\DeleteStory;
use Fedot\Backlog\Payload\DeleteStoryPayload;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class DeleteStoryTest extends RequestProcessorTestCase
{
    protected function getProcessorInstance(): ProcessorInterface
    {
        $this->initProcessorMocks();

        $processor = new DeleteStory($this->storiesRepositoryMock);

        return $processor;
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'delete-story';
    }

    public function testProcess()
    {
        $processor = $this->getProcessorInstance();

        $deleteStoryPayload = new DeleteStoryPayload();
        $deleteStoryPayload->storyId = 'story-id';
        $deleteStoryPayload->projectId = 'project-id';

        $this->storiesRepositoryMock->expects($this->once())
            ->method('deleteByIds')
            ->with(
                $this->equalTo('project-id'),
                $this->equalTo('story-id')
            )
            ->willReturn(new Success(true))
        ;

        $request = new Request(34, 'delete-story', 777, [
            'storyId' => 'story-id',
            'projectId' => 'project-id',
        ]);
        $request = $request->withAttribute('payloadObject', $deleteStoryPayload);
        $response = new Response($request->getId(), $request->getClientId());

        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals('story-deleted', $response->getType());
        $this->assertEquals([], $response->getPayload());
    }
}
