<?php
namespace Fedot\Backlog\Model;

use Fedot\Backlog\PayloadInterface;

class Story implements PayloadInterface
{
    /**
     * @var int
     */
    public $number;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $text;
}
