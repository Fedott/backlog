<?php declare(strict_types=1);

namespace Fedot\Backlog;

use Exception;
use Fedot\Backlog\Model\User;

class WebSocketConnectionAuthenticationService
{
    /**
     * @var User[]
     */
    protected $clientUser = [];

    /**
     * @param int  $clientId
     * @param User $user
     */
    public function authorizeClient(int $clientId, User $user)
    {
        $this->clientUser[$clientId] = $user;
    }

    /**
     * @param int $clientId
     */
    public function unauthorizeClient(int $clientId)
    {
        unset($this->clientUser[$clientId]);
    }

    /**
     * @param int $clientId
     *
     * @return bool
     */
    public function isAuthorizedClient(int $clientId): bool
    {
        return isset($this->clientUser[$clientId]);
    }

    /**
     * @param int $clientId
     *
     * @return User
     * @throws Exception
     */
    public function getAuthorizedUserForClient(int $clientId): User
    {
        if (!$this->isAuthorizedClient($clientId)) {
            throw new Exception("Client {$clientId} not authorized");
        }

        return $this->clientUser[$clientId];
    }
}
