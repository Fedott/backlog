<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\ProjectIdPayload;
use Fedot\Backlog\Payload\StoriesPayload;
use Fedot\Backlog\Request\Processor\GetStories;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Tests\Fedot\Backlog\RequestProcessorTestCase;

class GetStoriesTest extends RequestProcessorTestCase
{
    /**
     * @return GetStories
     */
    protected function getProcessorInstance()
    {
        $this->initProcessorMocks();

        return new GetStories($this->storiesRepositoryMock, $this->webSocketAuthServiceMock);
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
        $request1 = new Request(1, 1, 'get-stories');

        $request2 = new Request(1, 1, 'other');

        $request3 = new Request(1, 1, '');

        return [
            'ping type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
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

        $request = new Request(34, 777, 'get-stories', (array) $payload);
        $request = $request->withAttribute('payloadObject', $payload);

        $response = new Response($request->getId(), $request->getClientId());

        $this->storiesRepositoryMock->expects($this->once())
            ->method('getAllByProjectId')
            ->with($this->equalTo('project-id'))
            ->willReturn(new Success($stories))
        ;

        /** @var Response $response */
        $response = \Amp\wait($processor->process($request, $response));

        $this->assertEquals(34, $response->getRequestId());
        $this->assertEquals(777, $response->getClientId());
        $this->assertEquals('stories', $response->getType());

        $this->assertEquals((array) $stories, $response->getPayload()['stories']);
    }
}
