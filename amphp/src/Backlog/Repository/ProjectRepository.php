<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use function Amp\call;
use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\DataMapper\IdentityMap;
use Fedot\DataMapper\Redis\ModelManager;

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
        return call(function (User $user, Project $project) {
            $user->addProject($project);

            $identityMap = new IdentityMap();
            yield $this->modelManager->persist($project, $identityMap);
            yield $this->modelManager->persist($user, $identityMap);

            return true;
        }, $user, $project);
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
        return call(function (Project $project, User $user) {
            $project->share($user);

            $identityMap = new IdentityMap();
            yield $this->modelManager->persist($project, $identityMap);
            yield $this->modelManager->persist($user, $identityMap);

            return true;
        }, $project, $user);
    }
}
