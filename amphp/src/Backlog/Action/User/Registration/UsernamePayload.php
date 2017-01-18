<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\User\Registration;

use Fedot\Backlog\PayloadInterface;

class UsernamePayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $username;
}
