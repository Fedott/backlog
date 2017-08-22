<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Action\Project\Share;

use function Amp\Promise\all;
use function Amp\Promise\wait;
use Amp\Success;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Project\Share\ProjectShare;
use Fedot\Backlog\Action\Project\Share\ProjectSharePayload;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\ProjectRepository;
use Fedot\Backlog\Repository\UserRepository;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\ActionTestCase;

class ShareProjectTest extends ActionTestCase
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    protected function initActionMocks()
    {
        parent::initActionMocks();

        $this->userRepository = new UserRepository($this->modelManager);
        $this->projectRepository = new ProjectRepository($this->modelManager);
    }

    protected function getProcessorInstance(): ActionInterface
    {
        return new ProjectShare(
            $this->userRepository,
            $this->projectRepository
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'project/share';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return ProjectSharePayload::class;
    }

    public function testSharePositive()
    {
        $payload = new ProjectSharePayload();
        $payload->projectId = 'project-id';
        $payload->userId = 'user-id';

        $project = new Project('project-id', 'project 1');
        $user = new User('user-id', 'hash');

        all([
            $this->modelManager->persist($project),
            $this->modelManager->persist($user),
        ]);

        $request = $this->makeRequest(1, 2, 'project/share', $payload);
        $response = $this->makeResponse($request);

        $response = wait($this->action->process($request, $response));

        $this->assertResponseBasic($response, 1, 2, 'success');

        /** @var Project $actualProject */
        $actualProject = wait($this->projectRepository->get('project-id'));
        /** @var User $actualUser */
        $actualUser = wait($this->userRepository->get('user-id'));
        $this->assertEquals([$actualUser], $actualProject->getUsers());
        $this->assertEquals([$actualProject], $actualUser->getProjects());
    }

    public function testShareUserNotFound()
    {
        $payload = new ProjectSharePayload();
        $payload->projectId = 'project-id';
        $payload->userId = 'user-id';

        $project = new Project('project-id', 'project 1');

        wait($this->modelManager->persist($project));

        $request = $this->makeRequest(1, 2, 'project/share', $payload);
        $response = $this->makeResponse($request);

        $response = wait($this->action->process($request, $response));

        $this->assertResponseError($response, 1, 2, 'User or Project not found');

        /** @var Project $actualProject */
        $actualProject = wait($this->projectRepository->get('project-id'));
        $this->assertEquals([], $actualProject->getUsers());
    }

    public function testShareProjectNotFound()
    {
        $payload = new ProjectSharePayload();
        $payload->projectId = 'project-id';
        $payload->userId = 'user-id';

        $user = new User('user-id', 'hash');

        wait($this->modelManager->persist($user));

        $request = $this->makeRequest(1, 2, 'project/share', $payload);
        $response = $this->makeResponse($request);

        $response = wait($this->action->process($request, $response));

        $this->assertResponseError($response, 1, 2, 'User or Project not found');

        /** @var User $actualUser */
        $actualUser = wait($this->userRepository->get('user-id'));
        $this->assertEquals([], $actualUser->getProjects());
    }
}
