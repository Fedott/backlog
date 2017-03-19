<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Model;

use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Tests\Fedot\Backlog\BaseTestCase;

class StoryTest extends BaseTestCase
{
    public function test()
    {
        $story = new Story('story-id', 'test', 'story text', $this->createMock(Project::class));

        $this->assertEquals('story-id', $story->getId());

        $story->complete();

        $this->assertTrue($story->isCompleted());

        $requirement = new Requirement('id1', 'text', $story);
        $requirement2 = new Requirement('id1', 'text', $story);
        $requirement3 = new Requirement('id1', 'text', $story);

        $this->assertEquals([
            $requirement,
            $requirement2,
            $requirement3,
        ], $story->getRequirements());

        $story->removeRequirement($requirement2);

        $this->assertSame([
            $requirement,
            $requirement3,
        ], $story->getRequirements());
    }
}
