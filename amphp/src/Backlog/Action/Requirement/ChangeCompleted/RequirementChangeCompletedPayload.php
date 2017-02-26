<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Requirement\ChangeCompleted;

use Fedot\Backlog\PayloadInterface;

class RequirementChangeCompletedPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $requirementId;

    /**
     * @var bool
     */
    public $completed;

    public function __construct($requirementId, $completed)
    {
        $this->requirementId = $requirementId;
        $this->completed = $completed;
    }
}
