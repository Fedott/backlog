<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog;

use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\PayloadInterface;
use Fedot\Backlog\Repository\StoryRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class ActionTestCase extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->initActionMocks();
        $this->initActionInstance();
    }

    /**
     * @var ActionInterface
     */
    protected $action;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|StoryRepository
     */
    protected $storyRepositoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthServiceMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|NormalizerInterface
     */
    protected $normalizerMock;

    /**
     * @dataProvider providerSupportsRequest
     *
     * @param RequestInterface $request
     * @param bool $expectedResult
     */
    public function testSupportsRequest(RequestInterface $request, bool $expectedResult)
    {
        $processor = $this->getProcessorInstance();
        $actualResult = $processor->supportsRequest($request);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function providerSupportsRequest()
    {
        $request1 = new Request(1, $this->getExpectedValidRequestType(), 1);
        $request2 = new Request(1, 'other', 1);
        $request3 = new Request(1, '', 1);

        return [
            'valid type' => [$request1, true],
            'invalid type' => [$request2, false],
            'null type' => [$request3, false],
        ];
    }

    public function testExpectedPayload()
    {
        $processor = $this->getProcessorInstance();

        $this->assertEquals($this->getExpectedPayloadType(), $processor->getExpectedRequestPayload());
    }

    protected function initActionMocks()
    {
        $this->storyRepositoryMock = $this->createMock(StoryRepository::class);
        $this->webSocketAuthServiceMock = $this->createMock(WebSocketConnectionAuthenticationService::class);
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);
    }

    protected function initActionInstance()
    {
        $this->action = $this->getProcessorInstance();
    }

    protected function assertResponseBasic(Response $response, int $requestId, int $clientId, string $type)
    {
        $this->assertEquals($requestId, $response->getRequestId());
        $this->assertEquals($clientId, $response->getClientId());
        $this->assertEquals($type, $response->getType());
    }

    protected function assertResponseError(Response $response, int $requestId, int $clientId, string $message)
    {
        $this->assertResponseBasic($response, $requestId, $clientId, 'error');

        $this->assertEquals($message, $response->getPayload()['message']);
    }

    protected function makeRequest(
        int $requestId,
        int $clientId,
        string $requestType,
        PayloadInterface $payload
    ): RequestInterface {
        $request = new Request($requestId, $requestType, $clientId, (array)$payload);
        $request = $request->withAttribute('payloadObject', $payload);

        return $request;
    }

    protected function makeResponse(RequestInterface $request): ResponseInterface
    {
        return new Response($request->getId(), $request->getClientId());
    }

    abstract protected function getProcessorInstance(): ActionInterface;

    abstract protected function getExpectedValidRequestType(): string;

    abstract protected function getExpectedPayloadType(): ?string;
}
