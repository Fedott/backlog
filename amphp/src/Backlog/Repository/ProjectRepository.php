<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use Amp\Success;
use AsyncInterop\Loop;
use AsyncInterop\Promise;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\DataMapper\IdentityMap;
use Fedot\DataMapper\Redis\ModelManager;
use function Amp\wrap;

class ProjectRepository
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public function create(User $user, Project $project): Promise /** @yield bool */
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $user, $project) {
            $user->addProject($project);

            $identityMap = new IdentityMap();
            yield $this->modelManager->persist($project, $identityMap);
            yield $this->modelManager->persist($user, $identityMap);

            $promisor->resolve(true);
        }));

        return $promisor->promise();
    }

    public function getAllByUser(User $user): Promise /** @yield Project[] */
    {
        return new Success($user->getProjects());
    }

    public function get(string $id): Promise /** @yield Project */
    {
        return $this->modelManager->find(Project::class, $id);
    }

    public function addUser(Project $project, User $user): Promise /** @yield bool */
    {
        $deferred = new Deferred();

        Loop::defer(wrap(function () use ($deferred, $user, $project) {
            $project->share($user);

            $identityMap = new IdentityMap();
            yield $this->modelManager->persist($project, $identityMap);
            yield $this->modelManager->persist($user, $identityMap);

            $deferred->resolve(true);
        }));

        return $deferred->promise();
    }
}
