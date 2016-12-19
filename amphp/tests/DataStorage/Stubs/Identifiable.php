<?php declare(strict_types = 1);
namespace Tests\Fedot\DataStorage\Stubs;

use Fedot\DataStorage\Identifiable as IdentifiableInterface;

class Identifiable implements IdentifiableInterface
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
