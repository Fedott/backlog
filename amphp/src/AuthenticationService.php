<?php
namespace Fedot\Backlog;

use Exception;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Exception\UserNotFoundException;
use Fedot\Backlog\Model\User;

class AuthenticationService
{
    /**
     * @var array
     */
    private $userPasswords = [
        'testUser' => '$2y$10$kEYXDhRhNmS1mk226hurv.i23tmnFXuqa1LCMG7UoyhZ3nF/PK7a2',
        'fedot'    => '$2y$10$A2yCBJrvEdfxmc3CdQWMwezvqAZ1DYm9Cu9wKeGwDSoNS.WcJZUoC',
    ];

    /**
     * @param string $username
     * @param string $password
     *
     * @return array - [User, string]
     * @throws AuthenticationException
     */
    public function authByUsernamePassword(string $username, string $password): array
    {
        try {
            $user = $this->findUserByUsername($username);
        } catch (UserNotFoundException $exception) {
            throw new AuthenticationException("Invalid username or password");
        }

        if (!$this->passwordVerify($password, $user->password)) {
            throw new AuthenticationException("Invalid username or password");
        }

        $token = $this->getNewTokenForUser($user, 10*24*60*60);

        return [$user, $token];
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
     * @return string
     */
    private function getNewTokenForUser(User $user, int $ttl): string
    {
        return bin2hex(random_bytes(32));
    }
}
