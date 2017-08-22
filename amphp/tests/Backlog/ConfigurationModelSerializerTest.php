<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class ConfigurationModelSerializerTest extends TestCase
{
    /**
     * @var Serializer
     */
    protected $serializer;

    public function setUp()
    {
        parent::setUp();

        global $container;

        $this->serializer = $container->get('serializer');
    }

    public function testStorySerialize()
    {
        $project = new Project('project-id', 'project name');
        $story = new Story('story-id', 'story title', 'story text', $project);

        $actual = $this->serializer->normalize($story);
        $this->assertArraySubset([
            'id' => 'story-id',
            'title' => 'story title',
            'text' => 'story text',
            'completed' => false,
        ], $actual);
        $this->assertArrayNotHasKey('requirements', $actual);
    }

    public function testProjectSerialize()
    {
        $project = new Project('project-id', 'name');
        new Story('id1', 'id1', 'id1', $project);
        $story2 = new Story('id2', 'id1', 'id1', $project);
        new Story('id3', 'id1', 'id1', $project);

        new Requirement('id1', 'text', $story2);
        new Requirement('id2', 'text', $story2);
        new Requirement('id3', 'text', $story2);

        $actual = $this->serializer->normalize($project);
        $this->assertEquals([
            'id' => 'project-id',
            'name' => 'name'
        ], $actual);
        $this->assertArrayNotHasKey('stories', $actual);
        $this->assertArrayNotHasKey('users', $actual);
    }

    public function testRequirementSerialize()
    {
        $project = new Project('project-id', 'name');
        $story1 = new Story('id1', 'id1', 'id1', $project);

        $requirement1 = new Requirement('id1', 'text', $story1);
        new Requirement('id2', 'text', $story1);
        new Requirement('id3', 'text', $story1);

        $actual = $this->serializer->normalize($requirement1);
        $this->assertArraySubset([
            'id' => 'id1',
            'text' => 'text',
        ], $actual);
    }
}
