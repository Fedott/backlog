<?php

namespace Tests\Fedot\Backlog;

use Fedot\Backlog\Response\ResponseSender;
use Fedot\Backlog\StoriesRepository;
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
}
