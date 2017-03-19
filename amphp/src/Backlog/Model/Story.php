<?php declare(strict_types=1);
namespace Fedot\Backlog\Model;

use Fedot\DataMapper\Annotation\Field;
use Fedot\DataMapper\Annotation\Id;
use Fedot\DataMapper\Annotation\ReferenceMany;
use Fedot\DataMapper\Annotation\ReferenceOne;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

class Story
{
    /**
     * @Id
     *
     * @var string
     */
    private $id;

    /**
     * @Field
     *
     * @var string
     */
    private $title;

    /**
     * @Field
     *
     * @var string
     */
    private $text;

    /**
     * @Field
     *
     * @var bool
     */
    private $completed;

    /**
     * @ReferenceOne(target=Project::class)
     *
     * @var Project
     */
    private $project;

    /**
     * @MaxDepth(1)
     *
     * @ReferenceMany(target=Requirement::class)
     *
     * @var Requirement[]
     */
    private $requirements;

    public function __construct(string $id, string $title, string $text, Project $project, bool $completed = false)
    {
        $this->id = $id;
        $this->title = $title;
        $this->text = $text;
        $this->completed = $completed;
        $this->project = $project;
        $this->project->createStory($this);
        $this->requirements = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function complete(): void
    {
        $this->completed = true;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @return Requirement[]
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    public function createRequirement(Requirement $requirement): void
    {
        if (!in_array($requirement, $this->requirements, true)) {
            $this->requirements[] = $requirement;
        }
    }

    public function removeRequirement(Requirement $requirement): void
    {
        $key = array_search($requirement, $this->requirements, true);

        if (null !== $key) {
            unset($this->requirements[$key]);
            $this->requirements = array_values($this->requirements);
        }
    }

    public function edit(string $title, string $text): void
    {
        $this->title = $title;
        $this->text = $text;
    }
}
