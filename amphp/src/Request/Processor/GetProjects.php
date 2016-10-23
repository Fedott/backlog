<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\ProjectsPayload;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;

class GetProjects implements ProcessorInterface
{
    /**
     * @var ProjectsRepository
     */
    protected $projectRepository;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    /**
     * @param ProjectsRepository $projectRepository
     * @param WebSocketConnectionAuthenticationService $webSocketAuthService
     */
    public function __construct(
        ProjectsRepository $projectRepository,
        WebSocketConnectionAuthenticationService $webSocketAuthService
    ) {
        $this->projectRepository = $projectRepository;
        $this->webSocketAuthService = $webSocketAuthService;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool
    {
        return $request->type === $this->getSupportedType();
    }

    /**
     * @return string
     */
    public function getSupportedType(): string
    {
        return 'get-projects';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return EmptyPayload::class;
    }

    /**
     * @param Request $request
     *
     * @return Generator
     */
    public function process(Request $request): Generator
    {
        $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());
        $projects = yield $this->projectRepository->getAllByUser($user);

        $response = new Response();
        $response->requestId = $request->id;
        $response->type = 'projects';
        $response->payload = new ProjectsPayload();
        $response->payload->projects = $projects;

        $request->getResponseSender()->sendResponse($response, $request->getClientId());
    }
}
