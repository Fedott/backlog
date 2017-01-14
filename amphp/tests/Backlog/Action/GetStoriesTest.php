<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Action;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\ProjectIdPayload;
use Fedot\Backlog\Action\GetStories;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\ActionTestCase;

class GetStoriesTest extends ActionTestCase
{
    protected function getProcessorInstance(): ActionInterface
    {
        return new GetStories($this->storyRepositoryMock, $this->webSocketAuthServiceMock);
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'get-stories';
    }

    public function testProcess()
    {
        $stories = [
            new Story(),
            new Story(),
            new Story(),
        ];

        $processor = $this->getProcessorInstance();

        $payload = new ProjectIdPayload();
        $payload->projectId = 'project-id';

        $request = $this->makeRequest(34, 777, 'get-stories', $payload);
        $response = $this->makeResponse($request);

        $this->storyRepositoryMock->expects($this->once())
            ->method('getAllByProjectId')
            ->with($this->equalTo('project-id'))
            ->willReturn(new Success($stories))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertResponseBasic($response, 34, 777, 'stories');

        $this->assertEquals((array) $stories, $response->getPayload()['stories']);
    }
}
