<?php
namespace Fedot\Backlog\Payload;

use Fedot\Backlog\PayloadInterface;

class DeleteStoryPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $storyId;
}
