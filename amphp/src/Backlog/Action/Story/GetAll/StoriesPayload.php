<?php declare(strict_types=1);
namespace Fedot\Backlog\Action\Story\GetAll;

use Fedot\Backlog\Model\Story;
use Fedot\Backlog\PayloadInterface;

class StoriesPayload implements PayloadInterface
{
    /**
     * @var Story[]
     */
    public $stories;
}
