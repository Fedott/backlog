<?php
namespace Fedot\Backlog\Model;

use Fedot\Backlog\PayloadInterface;

class Story implements PayloadInterface
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
