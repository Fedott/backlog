<?php declare(strict_types = 1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use Amp\Promise;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\DataStorage\FetchManagerInterface;
use Fedot\DataStorage\PersistManagerInterface;
use Fedot\DataStorage\RelationshipManagerInterface;

class ProjectRepository
{
    /**
     * @var RelationshipManagerInterface
     */
    protected $relationshipManager;

    /**
     * @var PersistManagerInterface
     */
    protected $persistManager;

    /**
     * @var FetchManagerInterface
     */
    protected $fetchManager;

    public function __construct(
        RelationshipManagerInterface $indexManager,
        PersistManagerInterface $persistManager,
        FetchManagerInterface $fetchManager
    ) {
        $this->relationshipManager = $indexManager;
        $this->persistManager = $persistManager;
        $this->fetchManager = $fetchManager;
    }

    public function create(User $user, Project $project): Promise /** @yield bool */
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $user, $project) {
            yield $this->persistManager->persist($project);

            yield $this->relationshipManager->addManyToMany($user, $project);

            $promisor->succeed(true);
        });

        return $promisor->promise();
    }

    public function getAllByUser(User $user): Promise /** @yield Project[] */
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $user) {
            $projectIds = yield $this->relationshipManager->getIdsManyToMany($user, Project::class);

            $projects = yield $this->fetchManager->fetchCollectionByIds(Project::class, $projectIds);

            $promisor->succeed($projects);
        });

        return $promisor->promise();
    }

    public function get(string $id): Promise /** @yield Project */
    {
        return $this->fetchManager->fetchById(Project::class, $id);
    }

    public function addUser(Project $project, User $user): Promise /** @yield bool */
    {
        return $this->relationshipManager->addManyToMany($project, $user);
    }
}
