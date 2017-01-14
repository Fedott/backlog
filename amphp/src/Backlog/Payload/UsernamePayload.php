<?php declare(strict_types=1);
namespace Fedot\Backlog\Payload;

use Fedot\Backlog\PayloadInterface;

class UsernamePayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $username;
}
