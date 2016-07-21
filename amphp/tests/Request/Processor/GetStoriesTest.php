<?php
namespace Tests\Fedot\Backlog\Request\Processor;

use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Processor\GetStories;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Payload\StoriesPayload;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\StoriesRepository;
use Tests\Fedot\Backlog\BaseTestCase;

class GetStoriesTest extends BaseTestCase
{
    /**
     * @dataProvider providerSupportsRequest
     *
     * @param Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(Request $request, bool $expectedResult)
    {
        $processor = new GetStories($this->createMock(StoriesRepository::class));
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
        $responseSenderMock = $this->createMock(ResponseSender::class);
        $storiesRepositoryMock = $this->createMock(StoriesRepository::class);

        $stories = [
            new Story(),
            new Story(),
            new Story(),
        ];

        $processor = new GetStories($storiesRepositoryMock);

        $request = new Request();
        $request->id = 34;
        $request->type = 'get-stories';
        $request->setClientId(777);
        $request->setResponseSender($responseSenderMock);

        $storiesRepositoryMock->expects($this->once())
            ->method('getAll')
            ->willReturn(new Success($stories))
        ;

        $responseSenderMock->expects($this->once())
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

        $processor->process($request);

        $this->waitAsyncCode();
    }
}
