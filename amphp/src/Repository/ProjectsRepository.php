<?php declare(strict_types = 1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use Amp\Promise;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Infrastructure\Redis\FetchManager;
use Fedot\Backlog\Infrastructure\Redis\IndexManager;
use Fedot\Backlog\Infrastructure\Redis\PersistManager;

class ProjectsRepository
{
    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * @var PersistManager
     */
    protected $persistManager;

    /**
     * @var FetchManager
     */
    protected $fetchManager;

    /**
     * ProjectsRepository constructor.
     *
     * @param IndexManager $indexManager
     * @param PersistManager $persistManager
     * @param FetchManager $fetchManager
     */
    public function __construct(IndexManager $indexManager, PersistManager $persistManager, FetchManager $fetchManager)
    {
        $this->indexManager = $indexManager;
        $this->persistManager = $persistManager;
        $this->fetchManager = $fetchManager;
    }

    /**
     * @param User $user
     * @param Project $project
     *
     * @return Promise
     * @yield bool
     */
    public function create(User $user, Project $project): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $user, $project) {
            yield $this->persistManager->persist($project);

            yield $this->indexManager->addOneToMany($user, $project);

            $promisor->succeed(true);
        });

        return $promisor->promise();
    }

    /**
     * @param User $user
     *
     * @return Promise
     * @yield Project[]
     */
    public function getAllByUser(User $user): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $user) {
            $projectIds = yield $this->indexManager->getIdsOneToMany($user, Project::class);

            $projects = yield $this->fetchManager->fetchCollectionByIds(Project::class, $projectIds);

            $promisor->succeed($projects);
        });

        return $promisor->promise();
    }

    /**
     * @param string $id
     *
     * @return Promise
     * @yield Project
     */
    public function get(string $id): Promise
    {
        return $this->fetchManager->fetchById(Project::class, $id);
    }
}
