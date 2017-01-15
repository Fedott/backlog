<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Story\Move;

use Fedot\Backlog\PayloadInterface;

class MoveStoryPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $storyId;

    /**
     * @var string
     */
    public $beforeStoryId;

    /**
     * @var string
     */
    public $projectId;
}
