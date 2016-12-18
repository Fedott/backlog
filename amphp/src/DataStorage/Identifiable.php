<?php declare(strict_types = 1);
namespace Fedot\DataStorage;

interface Identifiable
{
    public function getId(): string;
}
