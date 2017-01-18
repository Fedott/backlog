<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\User\Login;

use Fedot\Backlog\PayloadInterface;

class TokenPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $token;
}
