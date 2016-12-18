<?php declare(strict_types = 1);
namespace Fedot\DataStorage\Redis;

use Amp\Promise;
use Amp\Redis\Client;
use Fedot\DataStorage\Identifiable;
use Fedot\DataStorage\KeyGeneratorInterface;
use Fedot\DataStorage\PersistManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PersistManager implements PersistManagerInterface
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

    public function persist(Identifiable $identifiable, bool $update = false): Promise
    {
        $json = $this->serializer->serialize($identifiable, 'json');

        $modelKey = $this->keyGenerator->getKeyForIdentifiable($identifiable);

        if ($update) {
            return $this->redisClient->set($modelKey, $json);
        } else {
            return $this->redisClient->setNx($modelKey, $json);
        }
    }

    public function remove(Identifiable $identifiable): Promise
    {
        return $this->redisClient->del($this->keyGenerator->getKeyForIdentifiable($identifiable));
    }
}