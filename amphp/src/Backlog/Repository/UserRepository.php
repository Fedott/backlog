<?php declare(strict_types = 1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use Amp\Promise;
use Fedot\Backlog\Infrastructure\Redis\FetchManager;
use Fedot\Backlog\Infrastructure\Redis\PersistManager;
use Fedot\Backlog\Model\User;

class UserRepository
{
    /**
     * @var FetchManager
     */
    protected $fetchManager;

    /**
     * @var PersistManager
     */
    protected $persistManager;

    public function __construct(FetchManager $fetchManager, PersistManager $persistManager)
    {
        $this->fetchManager = $fetchManager;
        $this->persistManager = $persistManager;
    }

    public function create(User $user): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $user) {
            $result = yield $this->persistManager->persist($user);

            $promisor->succeed($result);
        });

        return $promisor->promise();
    }

    public function get(string $username): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $username) {
            $user = yield $this->fetchManager->fetchById(User::class, $username);

            $promisor->succeed($user);
        });

        return $promisor->promise();
    }
}
