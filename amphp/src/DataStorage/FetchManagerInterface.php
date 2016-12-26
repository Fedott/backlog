<?php declare(strict_types = 1);

namespace Fedot\DataStorage;

use Amp\Promise;

interface FetchManagerInterface
{
    public function fetchById(string $className, string $id): Promise;

    public function fetchCollectionByIds(string $className, array $ids): Promise;
}