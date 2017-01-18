<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\User\Login;

use Fedot\Backlog\PayloadInterface;

class LoginSuccessPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $token;
    /**
     * @var
     */
    public $username;
}
