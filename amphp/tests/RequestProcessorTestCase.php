<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Fedot\Backlog\PayloadInterface;
use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;

abstract class RequestProcessorTestCase extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->initProcessorMocks();
    }

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|StoriesRepository
     */
    protected $storiesRepositoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthServiceMock;

    /**
     * @dataProvider providerSupportsRequest
     *
     * @param \Fedot\Backlog\WebSocket\Request $request
     * @param bool    $expectedResult
     */
    public function testSupportsRequest(\Fedot\Backlog\WebSocket\Request $request, bool $expectedResult)
    {
        $processor = $this->getProcessorInstance();
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new \Fedot\Backlog\WebSocket\Request(1, $this->getExpectedValidRequestType(), 1);
        $request2 = new \Fedot\Backlog\WebSocket\Request(1, 'other', 1);
        $request3 = new \Fedot\Backlog\WebSocket\Request(1, '', 1);

        return [
            'valid type' => [$request1, true],
            'invalid type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    protected function initProcessorMocks()
    {
        $this->storiesRepositoryMock = $this->createMock(StoriesRepository::class);
        $this->webSocketAuthServiceMock = $this->createMock(WebSocketConnectionAuthenticationService::class);
    }

    protected function assertResponseBasic(Response $response, int $requestId, int $clientId, string $type)
    {
        $this->assertEquals($requestId, $response->getRequestId());
        $this->assertEquals($clientId, $response->getClientId());
        $this->assertEquals($type, $response->getType());
    }

    protected function makeRequest(
        int $requestId,
        int $clientId,
        string $requestType,
        PayloadInterface $payload
    ):\Fedot\Backlog\WebSocket\Request
    {
        $request = new \Fedot\Backlog\WebSocket\Request($requestId, $requestType, $clientId, (array)$payload);
        $request = $request->withAttribute('payloadObject', $payload);

        return $request;
    }

    protected function makeResponse(\Fedot\Backlog\WebSocket\Request $request): Response
    {
        return new Response($request->getId(), $request->getClientId());
    }

    abstract protected function getProcessorInstance(): ProcessorInterface;
    abstract protected function getExpectedValidRequestType(): string;
}
