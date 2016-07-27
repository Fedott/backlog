<?php

namespace Fedot\Backlog\Payload;

use Fedot\Backlog\PayloadInterface;

class TokenPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $token;
}
