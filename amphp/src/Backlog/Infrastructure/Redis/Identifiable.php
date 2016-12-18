<?php declare(strict_types = 1);
namespace Fedot\Backlog\Infrastructure\Redis;

interface Identifiable
{
    public function getId(): string;
}
