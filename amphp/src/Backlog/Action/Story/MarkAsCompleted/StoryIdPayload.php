<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\MarkAsCompleted;

use Fedot\Backlog\PayloadInterface;

class StoryIdPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $storyId;
}
