<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use Amp\Promise;
use Amp\Deferred;
use Amp\Redis\Client;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Redis\FetchManager;
use Fedot\Backlog\Redis\IndexManager;
use Fedot\Backlog\Redis\PersistManager;
use Symfony\Component\Serializer\SerializerInterface;

class StoriesRepository
{

    /**
     * @var FetchManager
     */
    protected $fetchManager;

    /**
     * @var PersistManager
     */
    protected $persistManager;

    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * StoriesRepository constructor.
     *
     * @param FetchManager $fetchManager
     * @param PersistManager $persistManager
     * @param IndexManager $indexManager
     */
    public function __construct(FetchManager $fetchManager, PersistManager $persistManager, IndexManager $indexManager)
    {
        $this->fetchManager = $fetchManager;
        $this->persistManager = $persistManager;
        $this->indexManager = $indexManager;
    }

    /**
     * @param string $storyId
     *
     * @return string
     */
    protected function getKeyForStory(string $storyId)
    {
        return "{$this->storyKeyPrefix}{$storyId}";
    }

    /**
     * @param string $projectId
     *
     * @return string
     */
    protected function getKeyForStoriesSortDefault(string $projectId): string
    {
        return "project:{$projectId}:stories:sorted:default";
    }

    /**
     * @param Story $story
     *
     * @return string
     */
    protected function serializeStoryToJson(Story $story): string
    {
        return $this->serializer->serialize($story, 'json');
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

    /**
     * @param Project $project
     * @param Story $story
     * @param Story $positionStory
     *
     * @return Promise|bool
     */
    public function move(Project $project, Story $story, Story $positionStory)
    {
        return $this->indexManager->moveValueOnOneToManyIndex($project, $story, $positionStory);
    }
}
