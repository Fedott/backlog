<?php declare(strict_types = 1);
namespace Fedot\Backlog\Redis;

use Amp\Promise;
use Amp\Redis\Client;
use Symfony\Component\Serializer\SerializerInterface;

class PersistManager
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

    public function persist(Identifiable $identifiable): Promise
    {
        $json = $this->serializer->serialize($identifiable, 'json');

        $modelKey = $this->keyGenerator->getKeyForIdentifiable($identifiable);

        return $this->redisClient->set($modelKey, $json);
    }
}
