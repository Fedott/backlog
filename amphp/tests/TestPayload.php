<?php
namespace Tests\Fedot\Backlog;

use Fedot\Backlog\PayloadInterface;

class TestPayload implements PayloadInterface
{
    /**
     * @var int
     */
    public $field1;

    /**
     * @var string
     */
    public $field3;
}
