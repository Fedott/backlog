<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Requirement\Create;

use Fedot\Backlog\PayloadInterface;

class CreateRequirementPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $storyId;
}
