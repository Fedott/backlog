<?php

namespace Fedot\Backlog\Payload;

use Fedot\Backlog\PayloadInterface;

class MoveStoryPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $storyId;

    /**
     * @var
     */
    public $beforeStoryId;
}
