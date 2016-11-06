<?php declare(strict_types = 1);
namespace Fedot\Backlog\Infrastructure\Redis;

use Amp\Deferred;
use Amp\Promise;
use Amp\Redis\Client;

class IndexManager
{
    /**
     * @var KeyGenerator
     */
    protected $keyGenerator;

    /**
     * @var Client
     */
    protected $redisClient;

    public function __construct(KeyGenerator $keyGenerator, Client $redisClient)
    {
        $this->redisClient = $redisClient;
        $this->keyGenerator = $keyGenerator;
    }

    public function addToIndex(string $indexName, string $id): Promise
    {
        return $this->redisClient->lPush($indexName, $id);
    }

    public function getIdsFromIndex(string $indexName): Promise
    {
        return $this->redisClient->lRange(
            $indexName,
            0,
            -1
        );
    }

    public function addOneToMany(Identifiable $forModel, Identifiable $model): Promise
    {
        $indexName = $this->keyGenerator->getOneToManeIndexName($forModel, $model);

        return $this->addToIndex($indexName, $model->getId());
    }

    public function getIdsOneToMany(Identifiable $forModel, string $modelClassName): Promise
    {
        $indexName = $this->keyGenerator->getOneToManeIndexName($forModel, $modelClassName);

        return $this->getIdsFromIndex($indexName);
    }

    public function removeFromIndex(string $indexName, string $key): Promise
    {
        return $this->redisClient->lRem($indexName, $key);
    }

    public function moveValueOnIndex(string $indexName, string $targetId, string $positionId): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $targetId, $positionId, $indexName) {
            yield $this->redisClient->lRem($indexName, $targetId, 0);
            $insertResult = yield $this->redisClient->lInsert(
                $indexName,
                "before",
                $positionId,
                $targetId
            );

            if ($insertResult !== -1) {
                $promisor->succeed(true);
            } else {
                yield $this->redisClient->lPush($indexName, $targetId);

                $promisor->succeed(false);
            }
        });

        return $promisor->promise();
    }

    public function moveValueOnOneToManyIndex(
        Identifiable $forModel,
        Identifiable $model,
        Identifiable $positionModel
    ): Promise
    {
        $indexName = $this->keyGenerator->getOneToManeIndexName($forModel, $model);

        return $this->moveValueOnIndex($indexName,
            $this->keyGenerator->getKeyForIdentifiable($model),
            $this->keyGenerator->getKeyForIdentifiable($positionModel)
        );
    }

    public function removeOneToMany(Identifiable $forModel, Identifiable $model): Promise
    {
        $indexName = $this->keyGenerator->getOneToManeIndexName($forModel, $model);
        $modelKey = $this->keyGenerator->getKeyForIdentifiable($model);

        return $this->removeFromIndex($indexName, $modelKey);
    }
}
