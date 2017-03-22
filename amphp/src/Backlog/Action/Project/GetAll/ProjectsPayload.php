<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Project\GetAll;

use Fedot\Backlog\Model\Project;
use Fedot\Backlog\PayloadInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;

class ProjectsPayload implements PayloadInterface
{
    /**
     * @MaxDepth(1)
     *
     * @var Project[]
     */
    public $projects;
}
