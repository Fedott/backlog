<?php declare(strict_types = 1);
namespace Fedot\Backlog\Redis;

interface Identifiable
{
    public function getId(): string;
}
