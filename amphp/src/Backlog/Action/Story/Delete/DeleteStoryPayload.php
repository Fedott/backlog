<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\Delete;

use Fedot\Backlog\PayloadInterface;

class DeleteStoryPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $storyId;

    /**
     * @var string
     */
    public $projectId;
}
