<?php declare(strict_types=1);

namespace Fedot\Backlog\Payload;

use Fedot\Backlog\PayloadInterface;

class TokenPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $token;
}
