<?php
namespace Fedot\Backlog;

use Amp\Deferred;
use Amp\Promise;
use Amp\Redis\Client;
use Exception;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Exception\UserNotFoundException;
use Fedot\Backlog\Model\User;

class AuthenticationService
{
    /**
     * @var Client
     */
    protected $redisClient;

    /**
     * @var array
     */
    private $userPasswords = [
        'testUser' => '$2y$10$kEYXDhRhNmS1mk226hurv.i23tmnFXuqa1LCMG7UoyhZ3nF/PK7a2',
        'fedot'    => '$2y$10$A2yCBJrvEdfxmc3CdQWMwezvqAZ1DYm9Cu9wKeGwDSoNS.WcJZUoC',
    ];

    /**
     * AuthenticationService constructor.
     *
     * @param Client $redisClient
     */
    public function __construct(Client $redisClient)
    {
        $this->redisClient = $redisClient;
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

        \Amp\immediately(function () use ($username, $password, $deferred) {
            try {
                $user = $this->findUserByUsername($username);
            } catch (UserNotFoundException $exception) {
                $deferred->fail(new AuthenticationException("Invalid username or password"));

                return;
            }

            if (!$this->passwordVerify($password, $user->password)) {
                $deferred->fail(new AuthenticationException("Invalid username or password"));

                return;
            }

            $token = yield $this->getNewTokenForUser($user, 10*24*60*60);

            $deferred->succeed([$user, $token]);
        });

        return $deferred->promise();
    }

    /**
     * @param string $token
     *
     * @return User
     */
    public function authByToken(string $token): User
    {
    }

    /**
     * @param string $username
     *
     * @return User
     *
     * @throws Exception
     */
    private function findUserByUsername(string $username): User
    {
        if (!array_key_exists($username, $this->userPasswords)) {
            throw new UserNotFoundException('User not found');
        }

        $user = new User();
        $user->username = $username;
        $user->password = $this->userPasswords[$username];

        return $user;
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
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

        \Amp\immediately(function () use ($user, $ttl, $deferred) {
            do {
                $token = bin2hex(random_bytes(32));

                $uniqueTokenGenerated = yield $this->redisClient
                    ->set("auth:token:{$token}", $user->username, $ttl, false, 'NX')
                ;
            } while (!$uniqueTokenGenerated);

            $deferred->succeed($token);
        });

        return $deferred->promise();
    }
}
