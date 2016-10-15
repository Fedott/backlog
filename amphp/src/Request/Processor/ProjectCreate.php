<?php declare(strict_types = 1);
namespace Fedot\Backlog\Request\Processor;

use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Repository\ProjectsRepository;
use Fedot\Backlog\Request\Request;
use Fedot\Backlog\Response\Response;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;
use Generator;
use Ramsey\Uuid\UuidFactory;

class ProjectCreate implements ProcessorInterface
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
     * @param Request $request
     *
     * @return bool
     */
    public function supportsRequest(Request $request): bool
    {
        return $this->getSupportedType() === $request->type;
    }

    /**
     * @return string
     */
    public function getSupportedType(): string
    {
        return 'create-project';
    }

    /**
     * @return string - FQN class name implemented \Fedot\Backlog\PayloadInterface
     */
    public function getExpectedRequestPayload(): string
    {
        return Project::class;
    }

    /**
     * @param Request $request
     *
     * @return Generator
     */
    public function process(Request $request): Generator
    {
        /** @var Project $project */
        $project = $request->payload;
        $project->id = $this->uuidFactory->uuid4()->toString();

        $user = $this->webSocketAuthService->getAuthorizedUserForClient($request->getClientId());

        yield $this->projectRepository->create($user, $project);

        $response = new Response();
        $response->requestId = $request->id;
        $response->type = 'project-created';
        $response->payload = $project;

        $request->getResponseSender()->sendResponse($response, $request->getClientId());
    }
}
