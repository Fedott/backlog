<?php declare(strict_types=1);

namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use Amp\Success;
use AsyncInterop\Loop;
use AsyncInterop\Promise;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\DataMapper\ModelManagerInterface;
use function Amp\wrap;

class RequirementRepository
{
    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    public function __construct(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public function create(Story $story, Requirement $requirement): Promise /** @yield bool */
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $story, $requirement) {
            yield $this->modelManager->persist($requirement);
            yield $this->modelManager->persist($story);

            $promisor->resolve(true);
        }));

        return $promisor->promise();
    }

    public function save(Requirement $requirement): Promise /** @yield bool */
    {
        return $this->modelManager->persist($requirement);
    }

    public function getAllByStory(Story $story): Promise /** @yield Requirement[] */
    {
        return new Success($story->getRequirements());
    }

    public function get(string $id): Promise /** @yield Requirement|null */
    {
        return $this->modelManager->find(Requirement::class, $id);
    }
}
