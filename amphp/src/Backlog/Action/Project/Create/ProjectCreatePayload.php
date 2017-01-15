<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Project\Create;

use Fedot\Backlog\PayloadInterface;

class ProjectCreatePayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $name;
}
