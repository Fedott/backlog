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

    public function __construct(string $id, string $text)
    {
        $this->id = $id;
        $this->text = $text;
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
}
