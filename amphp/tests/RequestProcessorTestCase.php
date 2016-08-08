<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Fedot\Backlog\Request\Processor\ProcessorInterface;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\Repository\StoriesRepository;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use PHPUnit_Framework_MockObject_MockObject;

abstract class RequestProcessorTestCase extends BaseTestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ResponseSender
     */
    protected $responseSenderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|StoriesRepository
     */
    protected $storiesRepositoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthServiceMock;

    protected function initProcessorMocks()
    {
        $this->responseSenderMock = $this->createMock(ResponseSender::class);
        $this->storiesRepositoryMock = $this->createMock(StoriesRepository::class);
        $this->webSocketAuthServiceMock = $this->createMock(WebSocketConnectionAuthenticationService::class);
    }

    /**
     * @param ProcessorInterface $processor
     * @param Request $request
     */
    protected function startProcessMethod(ProcessorInterface $processor, Request $request)
    {
        \Amp\immediately(function () use ($processor, $request) {
            yield from $processor->process($request);
        });

        $this->waitAsyncCode();
    }
}
