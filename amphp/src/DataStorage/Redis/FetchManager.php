<?php declare(strict_types = 1);
namespace Fedot\DataStorage\Redis;

use Amp\Deferred;
use Amp\Failure;
use Amp\Promise;
use Amp\Redis\Client;
use Amp\Success;
use Fedot\DataStorage\FetchManagerInterface;
use Fedot\DataStorage\Identifiable;
use Fedot\DataStorage\KeyGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class FetchManager implements FetchManagerInterface
{
    /**
     * @var KeyGeneratorInterface
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

    public function __construct(KeyGeneratorInterface $keyGenerator, Client $redisClient, SerializerInterface $serializer)
    {
        $this->keyGenerator = $keyGenerator;
        $this->redisClient = $redisClient;
        $this->serializer = $serializer;
    }

    public function fetchById(string $className, string $id): Promise
    {
        if (!is_subclass_of($className, Identifiable::class)) {
            return new Failure(new \TypeError("{$className} not implemented " . Identifiable::class));
        }

        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $className, $id) {
            $key = $this->keyGenerator->getKeyForClassNameId($className, $id);

            $data = yield $this->redisClient->get($key);

            if (null === $data) {
                $result = null;
            } else {
                $result = $this->serializer->deserialize($data, $className, 'json');
            }

            $promisor->succeed($result);
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
