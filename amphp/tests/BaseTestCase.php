<?php
namespace Tests\Fedot\Backlog;

use PHPUnit_Framework_TestCase;

abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        \Amp\reactor($assign = new \Amp\NativeReactor);
    }
}
