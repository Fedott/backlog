<?php declare(strict_types = 1);

namespace Fedot\DataStorage;

interface KeyGeneratorInterface
{
    public function getKeyForIdentifiable(Identifiable $model): string;

    public function getKeyForClassNameId(string $className, string $id): string;

    public function getOneToManeIndexName(Identifiable $forModel, $model): string;
}