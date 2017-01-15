<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Project\Share;

use Amp\Promisor;
use Fedot\Backlog\Action\AbstractAction;
use Fedot\Backlog\Action\ErrorPayload;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\UserRepository;
use Fedot\Backlog\WebSocket\RequestInterface;
use Fedot\Backlog\WebSocket\ResponseInterface;

class ProjectShare extends AbstractAction
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    public function __construct(UserRepository $userRepository, ProjectRepository $projectRepository)
    {
        $this->userRepository = $userRepository;
        $this->projectRepository = $projectRepository;
    }

    protected function execute(Promisor $promisor, RequestInterface $request, ResponseInterface $response)
    {
        /** @var ProjectSharePayload $payload */
        $payload = $request->getAttribute('payloadObject');

        $project = yield $this->projectRepository->get($payload->projectId);
        $user = yield $this->userRepository->get($payload->userId);

        if ($project && $user) {
            yield $this->projectRepository->addUser($project, $user);

            $response = $response->withType('success');
        } else {
            $response = $response
                ->withType('error')
                ->withPayload((array) new ErrorPayload('User or Project not found'))
            ;
        }

        $promisor->succeed($response);
    }

    public function getSupportedType(): string
    {
        return 'project/share';
    }

    public function getExpectedRequestPayload(): string
    {
        return ProjectSharePayload::class;
    }
}
