<?php declare(strict_types=1);
namespace Fedot\Backlog\Payload;

use Fedot\Backlog\PayloadInterface;

class PongPayload implements PayloadInterface
{
    /**
     * @var bool
     */
    public $pong = true;
}
