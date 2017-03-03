<?php declare(strict_types=1);

namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use AsyncInterop\Loop;
use AsyncInterop\Promise;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\DataMapper\FetchManagerInterface;
use Fedot\DataMapper\PersistManagerInterface;
use Fedot\DataMapper\RelationshipManagerInterface;
use function Amp\wrap;

class RequirementRepository
{
    /**
     * @var PersistManagerInterface
     */
    protected $persistManager;

    /**
     * @var FetchManagerInterface
     */
    protected $fetchManager;

    /**
     * @var RelationshipManagerInterface
     */
    protected $relationshipManager;

    public function __construct(
        PersistManagerInterface $persistManager,
        FetchManagerInterface $fetchManager,
        RelationshipManagerInterface $relationshipManager
    ) {
        $this->persistManager = $persistManager;
        $this->fetchManager = $fetchManager;
        $this->relationshipManager = $relationshipManager;
    }

    public function create(Story $story, Requirement $requirement): Promise /** @yield bool */
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $story, $requirement) {
            yield $this->persistManager->persist($requirement);
            yield $this->relationshipManager->addOneToMany($story, $requirement);

            $promisor->resolve(true);
        }));

        return $promisor->promise();
    }

    public function save(Requirement $requirement): Promise /** @yield bool */
    {
        return $this->persistManager->persist($requirement, true);
    }

    public function getAllByStory(Story $story): Promise /** @yield Requirement[] */
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $story) {
            $requirementIds = yield $this->relationshipManager->getIdsOneToMany(
                $story,
                Requirement::class
            );
            $requirements = yield $this->fetchManager->fetchCollectionByIds(
                Requirement::class,
                $requirementIds
            );

            $promisor->resolve($requirements);
        }));

        return $promisor->promise();
    }

    public function get(string $id): Promise /** @yield Requirement|null */
    {
        return $this->fetchManager->fetchById(Requirement::class, $id);
    }
}
