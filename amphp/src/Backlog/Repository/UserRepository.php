<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use function Amp\wrap;
use AsyncInterop\Loop;
use AsyncInterop\Promise;
use Fedot\Backlog\Model\User;
use Fedot\DataStorage\FetchManagerInterface;
use Fedot\DataStorage\PersistManagerInterface;

class UserRepository
{
    /**
     * @var FetchManagerInterface
     */
    protected $fetchManager;

    /**
     * @var PersistManagerInterface
     */
    protected $persistManager;

    public function __construct(FetchManagerInterface $fetchManager, PersistManagerInterface $persistManager)
    {
        $this->fetchManager = $fetchManager;
        $this->persistManager = $persistManager;
    }

    public function create(User $user): Promise
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $user) {
            $result = yield $this->persistManager->persist($user);

            $promisor->resolve($result);
        }));

        return $promisor->promise();
    }

    public function get(string $username): Promise
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $username) {
            $user = yield $this->fetchManager->fetchById(User::class, $username);

            $promisor->resolve($user);
        }));

        return $promisor->promise();
    }
}
