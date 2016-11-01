<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Amp\Promisor;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\WebSocket\Request;
use Fedot\Backlog\WebSocket\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Ramsey\Uuid\UuidFactory;

class ProjectCreate extends AbstractProcessor
{
    /**
     * @var ProjectsRepository
     */
    protected $projectRepository;

    /**
     * @var UuidFactory
     */
    protected $uuidFactory;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    /**
     * CreateStory constructor.
     *
     * @param ProjectsRepository $projectRepository
     * @param UuidFactory $uuidFactory
     * @param WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
     */
    public function __construct(
        ProjectsRepository $projectRepository,
        UuidFactory $uuidFactory,
        WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
    ) {
        $this->projectRepository = $projectRepository;
        $this->uuidFactory = $uuidFactory;
        $this->webSocketAuthService = $webSocketConnectionAuthenticationService;
    }

    /**
     * @return string
     */
    public function getSupportedType(): string
    {
        return 'create-project';
    }

    public function getExpectedRequestPayload(): string
    {
        return Project::class;
    }

    protected function execute(Promisor $promisor, Request $request, Response $response)
    {
        /** @var Project $project */
        $project = $request->getAttribute('payloadObject');
        $project->id = $this->uuidFactory->uuid4()->toString();

        $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());

        yield $this->projectRepository->create($user, $project);

        $response = $response->withType('project-created');
        $response = $response->withPayload((array) $project);

        $promisor->succeed($response);
    }
}
