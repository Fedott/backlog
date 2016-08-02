<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog;

use PHPUnit_Framework_TestCase;

abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        \Amp\reactor($assign = new \Amp\NativeReactor);
    }

    protected function waitAsyncCode()
    {
        \Amp\tick();
        \Amp\tick();
        \Amp\tick();
    }
}
