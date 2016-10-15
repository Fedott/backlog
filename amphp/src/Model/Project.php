<?php declare(strict_types = 1);
namespace Fedot\Backlog\Model;

use Fedot\Backlog\Redis\Identifiable;

class Project implements Identifiable
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    public function getId(): string
    {
        return $this->id;
    }
}
