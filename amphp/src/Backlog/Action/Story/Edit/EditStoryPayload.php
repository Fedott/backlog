<?php declare(strict_types=1);

namespace Fedot\Backlog\Action\Story\Edit;

use Fedot\Backlog\PayloadInterface;

class EditStoryPayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $text;
}
