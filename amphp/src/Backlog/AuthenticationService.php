<?php declare(strict_types=1);
namespace Fedot\Backlog;

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;
use Amp\Redis\Client;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Exception\UserNotFoundException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\UserRepository;

class AuthenticationService
{
    /**
     * @var Client
     */
    protected $redisClient;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var array
     */
    private $userPasswords = [
        'testUser' => '$2y$10$kEYXDhRhNmS1mk226hurv.i23tmnFXuqa1LCMG7UoyhZ3nF/PK7a2',
        'fedot'    => '$2y$10$A2yCBJrvEdfxmc3CdQWMwezvqAZ1DYm9Cu9wKeGwDSoNS.WcJZUoC',
    ];

    public function __construct(Client $redisClient, UserRepository $userRepository)
    {
        $this->redisClient = $redisClient;
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return Promise
     * @yield array - [User, string]
     * @throws AuthenticationException
     */
    public function authByUsernamePassword(string $username, string $password): Promise
    {
        $deferred = new Deferred();

        Loop::defer(function () use ($username, $password, $deferred) {
            try {
                /** @var User $user */
                $user = yield $this->findUserByUsername($username);
            } catch (UserNotFoundException $exception) {
                $deferred->fail(new AuthenticationException('Invalid username or password'));

                return;
            }

            if (!$this->passwordVerify($password, $user->getPasswordHash())) {
                $deferred->fail(new AuthenticationException('Invalid username or password'));

                return;
            }

            $token = yield $this->getNewTokenForUser($user, 10*24*60*60);

            $deferred->resolve([$user, $token]);
        });

        return $deferred->promise();
    }

    /**
     * @param string $token
     *
     * @return Promise
     * @yield string - username
     */
    public function authByToken(string $token): Promise
    {
        $deferred = new Deferred();

        Loop::defer(function () use ($token, $deferred) {
            $username = yield $this->redisClient->get("auth:token:{$token}");

            if (null === $username) {
                $deferred->fail(new AuthenticationException("Invalid or expired token"));

                return;
            }

            $deferred->resolve($username);
        });

        return $deferred->promise();
    }

    public function findUserByUsername(string $username): Promise
    {
        $deferred = new Deferred();

        Loop::defer(function () use ($deferred, $username) {
            $user = yield $this->userRepository->get($username);
            if ($user) {
                $deferred->resolve($user);
                return;
            }

            if (!array_key_exists($username, $this->userPasswords)) {
                $deferred->fail(new UserNotFoundException('User not found'));
                return;
            }

            $user = new User(
                $username,
                $this->userPasswords[$username]
            );

            $deferred->resolve($user);
        });

        return $deferred->promise();
    }

    private function passwordVerify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * @param User $user
     * @param int  $ttl
     *
     * @return Promise
     * @yield string
     */
    private function getNewTokenForUser(User $user, int $ttl): Promise
    {
        $deferred = new Deferred();

        Loop::defer(function () use ($user, $ttl, $deferred) {
            do {
                $token = bin2hex(random_bytes(32));

                $uniqueTokenGenerated = yield $this->redisClient
                    ->set("auth:token:{$token}", $user->getUsername(), $ttl, false, 'NX')
                ;
            } while (!$uniqueTokenGenerated);

            $deferred->resolve($token);
        });

        return $deferred->promise();
    }
}
