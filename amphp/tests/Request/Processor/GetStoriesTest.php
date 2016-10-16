<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Payload\ProjectIdPayload;
use Fedot\Backlog\Payload\StoriesPayload;
use Fedot\Backlog\Request\Processor\GetStories;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
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
        $request1 = new Request();
        $request1->type = 'get-stories';

        $request2 = new Request();
        $request2->type = 'other';

        $request3 = new Request();

        return [
            'ping type' => [$request1, true],
            'other type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    public function testProcess()
    {
        $this->responseSenderMock = $this->createMock(ResponseSender::class);

        $stories = [
            new Story(),
            new Story(),
            new Story(),
        ];

        $processor = $this->getProcessorInstance();

        $request = new Request();
        $request->id = 34;
        $request->type = 'get-stories';
        $request->payload = new ProjectIdPayload();
        $request->payload->projectId = 'project-id';
        $request->setClientId(777);
        $request->setResponseSender($this->responseSenderMock);

        $this->storiesRepositoryMock->expects($this->once())
            ->method('getAllByProjectId')
            ->with($this->equalTo('project-id'))
            ->willReturn(new Success($stories))
        ;

        $this->responseSenderMock->expects($this->once())
            ->method('sendResponse')
            ->willReturnCallback(function (Response $response, $clientId = null) use ($stories) {
                $this->assertEquals(777, $clientId);
                $this->assertEquals(34, $response->requestId);
                $this->assertEquals('stories', $response->type);

                /** @var \Fedot\Backlog\Payload\StoriesPayload $response->payload */
                $this->assertInstanceOf(StoriesPayload::class, $response->payload);
                $this->assertEquals($stories, $response->payload->stories);
            })
        ;

        $this->startProcessMethod($processor, $request);
    }
}
