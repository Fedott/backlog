<?php declare(strict_types=1);

namespace Fedot\Backlog\Model;

use Fedot\DataStorage\Identifiable;

class User implements Identifiable
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    public function getId(): string
    {
        return $this->username;
    }
}