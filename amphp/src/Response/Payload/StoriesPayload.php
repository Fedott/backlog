<?php
namespace Fedot\Backlog\Response\Payload;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\PayloadInterface;

class StoriesPayload implements PayloadInterface
{
    /**
     * @var Story[]
     */
    public $stories;
}
