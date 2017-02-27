<?php declare(strict_types = 1);

namespace Fedot\Backlog\Model;

use Fedot\DataStorage\Identifiable;

class Requirement implements Identifiable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var bool
     */
    private $completed = false;

    public function __construct(string $id, string $text, bool $completed = false)
    {
        $this->id = $id;
        $this->text = $text;
        $this->completed = $completed;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function edit(string $text): void
    {
        $this->text = $text;
    }

    public function isCompleted()
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
