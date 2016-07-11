<?php
namespace Fedot\Backlog\Response\Payload;

use Fedot\Backlog\PayloadInterface;

class PongPayload implements PayloadInterface
{
    public $pong = true;
}
