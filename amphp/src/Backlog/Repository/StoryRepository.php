<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use function Amp\wrap;
use AsyncInterop\Loop;
use AsyncInterop\Promise;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\DataStorage\FetchManagerInterface;
use Fedot\DataStorage\PersistManagerInterface;
use Fedot\DataStorage\RelationshipManagerInterface;

class StoryRepository
{
    /**
     * @var FetchManagerInterface
     */
    protected $fetchManager;

    /**
     * @var PersistManagerInterface
     */
    protected $persistManager;

    /**
     * @var RelationshipManagerInterface
     */
    protected $indexManager;

    /**
     * @var ProjectRepository
     */
    protected $projectRepository;

    public function __construct(
        FetchManagerInterface $fetchManager,
        PersistManagerInterface $persistManager,
        RelationshipManagerInterface $indexManager,
        ProjectRepository $projectRepository
    ) {
        $this->fetchManager = $fetchManager;
        $this->persistManager = $persistManager;
        $this->indexManager = $indexManager;
        $this->projectRepository = $projectRepository;
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

        Loop::defer(wrap(function () use ($deferred, $project) {
            $storiesIds = yield $this->indexManager->getIdsOneToMany($project, Story::class);

            if (!empty($storiesIds)) {
                $stories = $this->fetchManager->fetchCollectionByIds(Story::class, $storiesIds);
            } else {
                $stories = [];
            }

            $deferred->resolve($stories);
        }));

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

        Loop::defer(wrap(function () use ($promisor, $story, $project) {
            $created = yield $this->persistManager->persist($story);

            if ($created) {
                yield $this->indexManager->addOneToMany($project, $story);

                $promisor->resolve(true);
            } else {
                $promisor->resolve(false);
            }
        }));

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

        Loop::defer(wrap(function () use ($promisor, $story, $project) {
            yield $this->indexManager->removeOneToMany($project, $story);

            yield $this->persistManager->remove($story);

            $promisor->resolve(true);
        }));

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
        return $this->indexManager->moveValueOnOneToMany($project, $story, $positionStory);
    }

    public function get(string $storyId): Promise
    {
        return $this->fetchManager->fetchById(Story::class, $storyId);
    }
}
