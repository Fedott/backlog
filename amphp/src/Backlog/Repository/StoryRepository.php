<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use Amp\Promise;
use Amp\Deferred;

use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;


use Fedot\DataStorage\Redis\PersistManager;
use Fedot\DataStorage\FetchManagerInterface;
use Fedot\DataStorage\IndexManagerInterface;


class StoryRepository
{
    /**
     * @var FetchManagerInterface
     */
    protected $fetchManager;

    /**
     * @var PersistManager
     */
    protected $persistManager;

    /**
     * @var IndexManagerInterface
     */
    protected $indexManager;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    public function __construct(
        FetchManagerInterface $fetchManager,
        PersistManager $persistManager,
        IndexManagerInterface $indexManager,
        ProjectRepository $projectRepository
    ) {
        $this->fetchManager = $fetchManager;
        $this->persistManager = $persistManager;
        $this->indexManager = $indexManager;
        $this->projectRepository = $projectRepository;
    }

    public function getAllByProjectId($projectId): Promise
    {
        $deferred = new Deferred;

        \Amp\immediately(function () use ($deferred, $projectId) {
            $project = yield $this->projectRepository->get($projectId);

            $stories = yield $this->getAllByProject($project);

            $deferred->succeed($stories);
        });

        return $deferred->promise();
    }

    /**
     * @param Project $project
     *
     * @return Promise
     * @yield Story[]
     */
    public function getAllByProject(Project $project): Promise
    {
        $deferred = new Deferred;

        \Amp\immediately(function () use ($deferred, $project) {
            $storiesIds = yield $this->indexManager->getIdsOneToMany($project, Story::class);

            $stories = $this->fetchManager->fetchCollectionByIds(Story::class, $storiesIds);

            $deferred->succeed($stories);
        });

        return $deferred->promise();
    }

    /**
     * @param Project $project
     * @param Story $story
     *
     * @return Promise|bool
     */
    public function create(Project $project, Story $story): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $story, $project) {
            $created = yield $this->persistManager->persist($story);

            if ($created) {
                yield $this->indexManager->addOneToMany($project, $story);

                $promisor->succeed(true);
            } else {
                $promisor->succeed(false);
            }
        });

        return $promisor->promise();
    }

    /**
     * @param Story $story
     *
     * @return Promise|bool
     */
    public function save(Story $story): Promise
    {
        return $this->persistManager->persist($story, true);
    }

    /**
     * @param Project $project
     * @param Story $story
     *
     * @return Promise|bool
     */
    public function delete(Project $project, Story $story): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function() use ($promisor, $story, $project) {
            yield $this->indexManager->removeOneToMany($project, $story);

            yield $this->persistManager->remove($story);

            $promisor->succeed(true);
        });

        return $promisor->promise();
    }

    public function deleteByIds(string $projectId, string $storyId): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function() use ($promisor, $projectId, $storyId) {
            $project = yield $this->projectRepository->get($projectId);
            $story = yield $this->get($storyId);

            $result = yield $this->delete($project, $story);

            $promisor->succeed($result);
        });

        return $promisor->promise();
    }

    /**
     * @param Project $project
     * @param Story $story
     * @param Story $positionStory
     *
     * @return Promise|bool
     */
    public function move(Project $project, Story $story, Story $positionStory): Promise
    {
        return $this->indexManager->moveValueOnOneToManyIndex($project, $story, $positionStory);
    }

    public function moveByIds(string $projectId, string $storyId, string $positionStoryId): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function() use ($promisor, $projectId, $storyId, $positionStoryId) {
            $project = yield $this->projectRepository->get($projectId);
            $story = yield $this->get($storyId);
            $positionStory = yield $this->get($positionStoryId);

            $result = yield $this->move($project, $story, $positionStory);

            $promisor->succeed($result);
        });

        return $promisor->promise();
    }

    public function get(string $storyId): Promise
    {
        return $this->fetchManager->fetchById(Story::class, $storyId);
    }
}
