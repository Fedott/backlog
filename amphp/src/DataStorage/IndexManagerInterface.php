<?php declare(strict_types = 1);

namespace Fedot\DataStorage;

use Amp\Promise;

interface IndexManagerInterface
{
    public function addOneToMany(Identifiable $forModel, Identifiable $model): Promise;

    public function getIdsOneToMany(Identifiable $forModel, string $modelClassName): Promise;

    public function moveValueOnIndex(string $indexName, string $targetId, string $positionId): Promise;

    public function moveValueOnOneToManyIndex(
        Identifiable $forModel,
        Identifiable $model,
        Identifiable $positionModel
    ): Promise;

    public function removeOneToMany(Identifiable $forModel, Identifiable $model): Promise;
}