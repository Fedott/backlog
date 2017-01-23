<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Model;

use Fedot\Backlog\Model\Story;
use Fedot\DataStorage\Identifiable;
use Tests\Fedot\Backlog\BaseTestCase;

class StoryTest extends BaseTestCase
{
    public function test()
    {
        $story = new Story();
        $story->id = 'test-id';

        $this->assertInstanceOf(Identifiable::class, $story);
        $this->assertEquals('test-id', $story->getId());
    }
}
