<?php declare(strict_types = 1);
namespace Fedot\Backlog\Model;

use Fedot\Backlog\PayloadInterface;
use Fedot\DataStorage\Identifiable;

class Project implements Identifiable, PayloadInterface
{
    /**
     * @var string|null
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
