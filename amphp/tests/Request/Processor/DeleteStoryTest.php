<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Request\Processor\DeleteStory;
use Fedot\Backlog\Payload\DeleteStoryPayload;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class DeleteStoryTest extends RequestProcessorTestCase
{
    /**
     * @return DeleteStory
     */
    protected function getProcessorInstance()
    {
        $this->initProcessorMocks();

        $processor = new DeleteStory($this->storiesRepositoryMock);

        return $processor;
    }

    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = $this->getProcessorInstance();
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request(1, 1, 'delete-story');
        $request2 = new Request(1, 1, 'other');
        $request3 = new Request(1, 1, '');

        return [
            'delete-story type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
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

        $request = new Request(34, 777, 'delete-story', [
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
