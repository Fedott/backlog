<?php declare(strict_types = 1);

namespace Fedot\Backlog\Action\Project\Share;

use Fedot\Backlog\PayloadInterface;

class ProjectSharePayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $projectId;
}
