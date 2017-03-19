<?php declare(strict_types = 1);

namespace Fedot\Backlog\Model;

use Fedot\DataMapper\Annotation\Field;
use Fedot\DataMapper\Annotation\Id;
use Fedot\DataMapper\Annotation\ReferenceOne;

class Requirement
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
    private $text;

    /**
     * @ReferenceOne(target=Story::class)
     *
     * @var Story
     */
    private $story;

    /**
     * @Field
     *
     * @var bool
     */
    private $completed;

    public function __construct(string $id, string $text, Story $story, bool $completed = false)
    {
        $this->id = $id;
        $this->text = $text;
        $this->completed = $completed;

        $this->story = $story;
        $this->story->createRequirement($this);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getStory(): Story
    {
        return $this->story;
    }

    public function edit(string $text): void
    {
        $this->text = $text;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function complete(): void
    {
        $this->completed = true;
    }

    public function incomplete(): void
    {
        $this->completed = false;
    }
}
