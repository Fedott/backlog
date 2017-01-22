<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Project\GetAll;

use Amp\Deferred as Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Action\EmptyPayload;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProjectsGetAll extends AbstractAction
{
    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    /**
     * @var WebSocketConnectionAuthenticationService
     */
    protected $webSocketAuthService;

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    public function __construct(
        ProjectRepository $projectRepository,
        WebSocketConnectionAuthenticationService $webSocketAuthService,
        NormalizerInterface $normalizer
    ) {
        $this->projectRepository = $projectRepository;
        $this->webSocketAuthService = $webSocketAuthService;
        $this->normalizer = $normalizer;
    }

    public function getSupportedType(): string
    {
        return 'get-projects';
    }

    public function getExpectedRequestPayload(): string
    {
        return EmptyPayload::class;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());
        $projects = yield $this->projectRepository->getAllByUser($user);

        $payload = new ProjectsPayload();
        $payload->projects = $projects;

        $response = $response->withType('projects');
        $response = $response->withPayload($this->normalizer->normalize($payload));

        $promisor->resolve($response);
    }
}
