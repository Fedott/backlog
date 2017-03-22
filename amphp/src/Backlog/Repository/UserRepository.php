<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;
use Fedot\Backlog\Model\User;
use Fedot\DataMapper\ModelManagerInterface;
use function Amp\wrap;

class UserRepository
{
    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    public function __construct(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public function create(User $user): Promise
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $user) {
            $loadedUser = yield $this->modelManager->find(User::class, $user->getUsername());

            if (null === $loadedUser) {
                $result = yield $this->modelManager->persist($user);
            } else {
                $result = false;
            }

            $promisor->resolve($result);
        }));

        return $promisor->promise();
    }

    public function get(string $username): Promise
    {
        $promisor = new Deferred();

        Loop::defer(wrap(function () use ($promisor, $username) {
            $user = yield $this->modelManager->find(User::class, $username);

            $promisor->resolve($user);
        }));

        return $promisor->promise();
    }
}
