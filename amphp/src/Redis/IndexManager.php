<?php declare(strict_types = 1);
namespace Fedot\Backlog\Redis;

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

    public function addToIndex($indexName, $id): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($indexName, $id, $promisor) {
            yield $this->redisClient->lPush($indexName, $id);

            $promisor->succeed();
        });

        return $promisor->promise();
    }

    public function getIdsFromIndex($indexName): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $indexName) {
            $ids = yield $this->redisClient->lRange(
                $indexName,
                0,
                -1
            );

            $promisor->succeed($ids);
        });

        return $promisor->promise();
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
}
