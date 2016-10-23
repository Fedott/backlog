<?php declare(strict_types = 1);
namespace Fedot\Backlog\Payload;

use Fedot\Backlog\Model\Project;
use Fedot\Backlog\PayloadInterface;

class ProjectsPayload implements PayloadInterface
{
    /**
     * @var Project[]
     */
    public $projects;
}
