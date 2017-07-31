<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Requirement\Save;

use Fedot\Backlog\PayloadInterface;

class SavePayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $text;
}
