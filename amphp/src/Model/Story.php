<?php declare(strict_types=1);
namespace Fedot\Backlog\Model;

use Fedot\Backlog\PayloadInterface;
use Fedot\Backlog\Infrastructure\Redis\Identifiable;

class Story implements PayloadInterface, Identifiable
{
    /**
     * @var string
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
     * @var string|null
     */
    public $projectId;

    public function getId(): string
    {
        return $this->id;
    }
}
