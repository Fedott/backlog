<?php declare(strict_types = 1);

namespace Fedot\DataStorage;

use Amp\Promise;

interface RelationshipManagerInterface
{
    public function addOneToMany(Identifiable $forModel, Identifiable $model): Promise;

    public function getIdsOneToMany(Identifiable $forModel, string $modelClassName): Promise;

    public function removeOneToMany(Identifiable $forModel, Identifiable $model): Promise;

    public function moveValueOnOneToMany(
        Identifiable $forModel,
        Identifiable $model,
        Identifiable $positionModel
    ): Promise;
}
