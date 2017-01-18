<?php declare(strict_types=1);
namespace Fedot\Backlog\Action;

use Fedot\Backlog\PayloadInterface;

class PongPayload implements PayloadInterface
{
    /**
     * @var bool
     */
    public $pong = true;
}
