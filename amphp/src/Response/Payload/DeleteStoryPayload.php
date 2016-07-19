<?php
namespace Fedot\Backlog\Response\Payload;

use Fedot\Backlog\PayloadInterface;

class DeleteStoryPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $storyId;
}
