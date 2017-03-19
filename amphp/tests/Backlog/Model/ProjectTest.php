<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Model;

use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Story;
use LogicException;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function test()
    {
        $project = new Project('id1', 'name');

        $story1 = new Story('id1', 'title', 'text', $project);
        $story2 = new Story('id2', 'title', 'text', $project);
        $story3 = new Story('id3', 'title', 'text', $project);

        $story4 = new Story('id3', 'title', 'text', $this->createMock(Project::class));

        $this->assertSame([
            $story3,
            $story2,
            $story1,
        ], $project->getStories());

        $project->moveStoryBeforeStory($story1, $story3);

        $this->assertSame([
            $story3,
            $story1,
            $story2,
        ], $project->getStories());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('One of stories not found in project');

        $project->moveStoryBeforeStory($story3, $story4);
    }
}
