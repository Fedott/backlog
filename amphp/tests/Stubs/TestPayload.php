<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog\Stubs;

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
