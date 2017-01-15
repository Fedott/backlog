<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\User\Login;

use Fedot\Backlog\PayloadInterface;

class UsernamePasswordPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;
}
