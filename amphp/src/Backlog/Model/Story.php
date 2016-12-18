<?php declare(strict_types=1);
namespace Fedot\Backlog\Model;

use Fedot\Backlog\PayloadInterface;
use Fedot\DataStorage\Identifiable;

class Story implements PayloadInterface, Identifiable
{
    /**
     * @var string|null
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $text;

    /**
     * @var bool
     */
    public $isCompleted = false;

    /**
     * @var string|null
     */
    public $projectId;

    public function getId(): string
    {
        return $this->id;
    }
}
