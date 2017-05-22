<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Project\Create;

use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Ramsey\Uuid\UuidFactory;

class ProjectCreate extends AbstractAction
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
        return ProjectCreatePayload::class;
    }

    protected function execute(RequestInterface $request, ResponseInterface $response)
    {
        /** @var ProjectCreatePayload $createProjectPayload */
        $createProjectPayload = $request->getAttribute('payloadObject');

        $project = new Project(
            $this->uuidFactory->uuid4()->toString(),
            $createProjectPayload->name
        );

        $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());

        yield $this->projectRepository->create($user, $project);

        $response = $response->withType('project-created');
        $response = $response->withPayload([
            'id' => $project->getId(),
            'name' => $project->getName()
        ]);

        return $response;
    }
}
