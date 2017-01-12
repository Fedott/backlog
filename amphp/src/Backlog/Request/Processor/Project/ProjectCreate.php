<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor\Project;

use Amp\Promisor;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Payload\CreateProjectPayload;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Request\Processor\AbstractProcessor;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Ramsey\Uuid\UuidFactory;

class ProjectCreate extends AbstractProcessor
{
    /**
     * @var ProjectRepository
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

    public function __construct(
        ProjectRepository $projectRepository,
        UuidFactory $uuidFactory,
        WebSocketConnectionAuthenticationService $webSocketConnectionAuthenticationService
    ) {
        $this->projectRepository = $projectRepository;
        $this->uuidFactory = $uuidFactory;
        $this->webSocketAuthService = $webSocketConnectionAuthenticationService;
    }

    public function getSupportedType(): string
    {
        return 'create-project';
    }

    public function getExpectedRequestPayload(): string
    {
        return CreateProjectPayload::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var CreateProjectPayload $createProjectPayload */
        $createProjectPayload = $request->getAttribute('payloadObject');

        $project = new Project(
            $this->uuidFactory->uuid4()->toString(),
            $createProjectPayload->name
        );

        $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());

        yield $this->projectRepository->create($user, $project);

        $response = $response->withType('project-created');
        $response = $response->withPayload(['id' => $project->getId(), 'name' => $project->getName()]);

        $promisor->succeed($response);
    }
}
