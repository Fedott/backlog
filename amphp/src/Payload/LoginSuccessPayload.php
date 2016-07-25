<?php

namespace Fedot\Backlog\Payload;

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
