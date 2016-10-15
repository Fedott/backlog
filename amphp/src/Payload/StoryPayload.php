<?php declare(strict_types = 1);
namespace Fedot\Backlog\Payload;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\PayloadInterface;

class StoryPayload implements PayloadInterface
{
    /**
     * @var array
     */
    public $story;

    /**
     * @var string
     */
    public $projectId;
}
