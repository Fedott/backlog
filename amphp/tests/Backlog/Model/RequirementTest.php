<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Model;

use Fedot\Backlog\Model\Requirement;
use Fedot\DataMapper\Identifiable;
use PHPUnit\Framework\TestCase;

class RequirementTest extends TestCase
{
    public function test()
    {
        $requirement = new Requirement('id', 'text');

        $this->assertInstanceOf(Identifiable::class, $requirement);

        $this->assertEquals('id', $requirement->getId());
        $this->assertEquals('text', $requirement->getText());
        $this->assertFalse($requirement->isCompleted());

        $requirement->edit('new text');
        $this->assertEquals('id', $requirement->getId());
        $this->assertEquals('new text', $requirement->getText());
        $this->assertFalse($requirement->isCompleted());

        $requirement->complete();
        $this->assertTrue($requirement->isCompleted());

        $requirement->incomplete();
        $this->assertFalse($requirement->isCompleted());
    }
}
