<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use function Amp\call;
use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use Fedot\DataMapper\IdentityMap;
use Fedot\DataMapper\Redis\ModelManager;

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
        return call(function (Project $project, Story $story) {
            $identityMap = new IdentityMap();
            yield $this->modelManager->persist($story, $identityMap);
            yield $this->modelManager->persist($project, $identityMap);

            return true;
        }, $project, $story);
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
        return call(function (Project $project, Story $story) {
            $project->removeStory($story);

            yield $this->modelManager->remove($story);
            yield $this->modelManager->persist($project);

            return true;
        }, $project, $story);
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
