<?php declare(strict_types=1);

namespace Fedot\Backlog\Repository;

use function Amp\call;
use Amp\Promise;
use Amp\Success;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\DataMapper\ModelManagerInterface;

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
        return call(function (Story $story, Requirement $requirement) {
            yield $this->modelManager->persist($requirement);
            yield $this->modelManager->persist($story);

            return true;
        }, $story, $requirement);
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
