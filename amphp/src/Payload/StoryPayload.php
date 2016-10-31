<?php declare(strict_types = 1);
namespace Fedot\Backlog\Payload;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\PayloadInterface;
use Fedot\Backlog\Serializer\Annotation\NestedClass;

class StoryPayload implements PayloadInterface
{
    /**
     * @var Story
     */
    public $story;

    /**
     * @var string
     */
    public $projectId;
}
