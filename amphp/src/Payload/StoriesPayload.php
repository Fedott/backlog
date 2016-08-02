<?php declare(strict_types=1);
namespace Fedot\Backlog\Payload;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\PayloadInterface;

class StoriesPayload implements PayloadInterface
{
    /**
     * @var Story[]
     */
    public $stories;
}
