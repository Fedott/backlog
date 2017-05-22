<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use function Amp\call;
use Amp\Promise;
use Fedot\Backlog\Model\User;
use Fedot\DataMapper\ModelManagerInterface;

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
        return call(function (User $user) {
            $loadedUser = yield $this->modelManager->find(User::class, $user->getUsername());

            if (null === $loadedUser) {
                $result = yield $this->modelManager->persist($user);
            } else {
                $result = false;
            }

            return $result;
        }, $user);
    }

    public function get(string $username): Promise
    {
        return call(function (string $username) {
            $user = yield $this->modelManager->find(User::class, $username);

            return $user;
        }, $username);
    }
}
