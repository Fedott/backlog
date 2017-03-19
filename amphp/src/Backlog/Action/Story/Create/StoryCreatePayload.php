<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\Create;

use Fedot\Backlog\PayloadInterface;

class StoryCreatePayload implements PayloadInterface
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $projectId;
}
