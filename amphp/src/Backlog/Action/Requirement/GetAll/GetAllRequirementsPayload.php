<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Requirement\GetAll;

use Fedot\Backlog\PayloadInterface;

class GetAllRequirementsPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $storyId;

    public function __construct(string $storyId)
    {
        $this->storyId = $storyId;
    }
}
