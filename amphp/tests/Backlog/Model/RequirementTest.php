<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Model;

use Fedot\Backlog\Model\Requirement;
use Fedot\DataStorage\Identifiable;

class RequirementTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $requirement = new Requirement('id', 'text');

        $this->assertInstanceOf(Identifiable::class, $requirement);

        $this->assertEquals('id', $requirement->getId());
        $this->assertEquals('text', $requirement->getText());

        $requirement->edit('new text');

        $this->assertEquals('id', $requirement->getId());
        $this->assertEquals('new text', $requirement->getText());
    }
}
