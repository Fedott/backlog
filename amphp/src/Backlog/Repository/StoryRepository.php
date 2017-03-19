<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use Amp\Success;
use AsyncInterop\Loop;
use AsyncInterop\Promise;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\DataMapper\FetchManagerInterface;
use Fedot\DataMapper\IdentityMap;
use Fedot\DataMapper\PersistManagerInterface;
use Fedot\DataMapper\Redis\ModelManager;
use Fedot\DataMapper\RelationshipManagerInterface;
use function Amp\wrap;

class StoryRepository
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param Project $project
     *
     * @return Promise
     * @yield Story[]
     */
    public function getAllByProject(Project $project): Promise
    {
        return new Success($project->getStories());
    }

    /**
     * @param Project $project
     * @param Story $story
     *
     * @return Promise
     * @yield bool
     */
    public function create(Project $project, Story $story): Promise
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $story, $project) {
            $identityMap = new IdentityMap();
            yield $this->modelManager->persist($story, $identityMap);
            yield $this->modelManager->persist($project, $identityMap);

            $promisor->resolve(true);
        }));

        return $promisor->promise();
    }

    /**
     * @param Story $story
     *
     * @return Promise
     * @yield bool
     */
    public function save(Story $story): Promise
    {
        return $this->modelManager->persist($story);
    }

    /**
     * @param Project $project
     * @param Story $story
     *
     * @return Promise
     * @yield bool
     */
    public function delete(Project $project, Story $story): Promise
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $story, $project) {
            $project->removeStory($story);

            yield $this->modelManager->remove($story);
            yield $this->modelManager->persist($project);

            $promisor->resolve(true);
        }));

        return $promisor->promise();
    }

    /**
     * @param Project $project
     * @param Story $story
     * @param Story $positionStory
     *
     * @return Promise
     * @yield bool
     */
    public function move(Project $project, Story $story, Story $positionStory): Promise
    {
        $project->moveStoryBeforeStory($story, $positionStory);

        return $this->modelManager->persist($project);
    }

    public function get(string $storyId): Promise /** @yield Story|null */
    {
        return $this->modelManager->find(Story::class, $storyId);
    }
}
