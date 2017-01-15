<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\Create;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\PayloadInterface;

class StoryCreatePayload implements PayloadInterface
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
