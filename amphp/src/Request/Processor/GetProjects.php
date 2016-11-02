<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Payload\EmptyPayload;
use Fedot\Backlog\Payload\ProjectsPayload;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

class GetProjects extends AbstractProcessor
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

    public function getSupportedType(): string
    {
        return 'get-projects';
    }

    public function getExpectedRequestPayload(): string
    {
        return EmptyPayload::class;
    }

    protected function execute(Promisor $promisor, Request $request, Response $response)
    {
        $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());
        $projects = yield $this->projectRepository->getAllByUser($user);

        $payload = new ProjectsPayload();
        $payload->projects = $projects;

        $response = $response->withType('projects');
        $response = $response->withPayload((array) $payload);

        $promisor->succeed($response);
    }
}
