<?php declare(strict_types=1);
namespace Fedot\Backlog;

use function Amp\call;
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
        return call(function (string $username, string $password) {
            try {
                /** @var User $user */
                $user = yield $this->findUserByUsername($username);
            } catch (UserNotFoundException $exception) {
                throw new AuthenticationException('Invalid username or password');
            }

            if (!$this->passwordVerify($password, $user->getPasswordHash())) {
                throw new AuthenticationException('Invalid username or password');
            }

            $token = yield $this->getNewTokenForUser($user, 10*24*60*60);

            return [$user, $token];
        }, $username, $password);
    }

    /**
     * @param string $token
     *
     * @return Promise
     * @yield string - username
     */
    public function authByToken(string $token): Promise
    {
        return call(function (string $token) {
            $username = yield $this->redisClient->get("auth:token:{$token}");

            if (null === $username) {
                throw new AuthenticationException("Invalid or expired token");
            }

            return $username;
        }, $token);
    }

    public function findUserByUsername(string $username): Promise
    {
        return call(function (string $username) {
            $user = yield $this->userRepository->get($username);
            if ($user) {
                return $user;
            }

            if (!array_key_exists($username, $this->userPasswords)) {
                throw new UserNotFoundException('User not found');
            }

            $user = new User(
                $username,
                $this->userPasswords[$username]
            );

            return $user;
        }, $username);
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
        return call(function (User $user, int $ttl) {
            do {
                $token = bin2hex(random_bytes(32));

                $uniqueTokenGenerated = yield $this->redisClient
                    ->set("auth:token:{$token}", $user->getUsername(), $ttl, false, 'NX')
                ;
            } while (!$uniqueTokenGenerated);

            return $token;
        }, $user, $ttl);
    }
}
