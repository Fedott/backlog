<?php declare(strict_types = 1);
namespace Fedot\Backlog\Infrastructure\Redis;

use Amp\Deferred;
use Amp\Promise;
use Amp\Redis\Client;
use Amp\Success;
use Symfony\Component\Serializer\SerializerInterface;

class FetchManager
{
    /**
     * @var KeyGenerator
     */
    protected $keyGenerator;

    /**
     * @var Client
     */
    protected $redisClient;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(KeyGenerator $keyGenerator, Client $redisClient, SerializerInterface $serializer)
    {
        $this->keyGenerator = $keyGenerator;
        $this->redisClient = $redisClient;
        $this->serializer = $serializer;
    }

    public function fetchById(string $className, string $id): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $className, $id) {
            $key = $this->keyGenerator->getKeyForClassNameId($className, $id);

            $data = yield $this->redisClient->get($key);

            $model = $this->serializer->deserialize($data, $className, 'json');

            $promisor->succeed($model);
        });

        return $promisor->promise();
    }

    public function fetchCollectionByIds(string $className, array $ids): Promise
    {
        if (empty($ids)) {
            return new Success([]);
        }

        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $className, $ids) {

            $keys = array_map(function ($id) use ($className) {
                return $this->keyGenerator->getKeyForClassNameId($className, $id);
            }, $ids);

            $rawModels = yield $this->redisClient->mGet($keys);

            $models = array_map(function ($rawModel) use ($className) {
                return $this->serializer->deserialize($rawModel, $className, 'json');
            }, $rawModels);

            $promisor->succeed($models);
        });

        return $promisor->promise();
    }
}