<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\GetAll;

use Fedot\Backlog\PayloadInterface;

class ProjectIdPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $projectId;
}
