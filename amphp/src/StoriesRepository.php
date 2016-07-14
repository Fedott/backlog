<?php
namespace Fedot\Backlog;

use Amp\Promise;
use Amp\Deferred;
use Amp\Redis\Client;
use Fedot\Backlog\Model\Story;
use Symfony\Component\Serializer\Serializer;

class StoriesRepository
{
    /**
     * @var Client
     */
    protected $redisClient;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * StoriesRepository constructor.
     *
     * @param Client     $redisClient
     * @param Serializer $serializer
     */
    public function __construct(Client $redisClient, Serializer $serializer)
    {
        $this->redisClient = $redisClient;
        $this->serializer  = $serializer;
    }

    /**
     * @return Promise|Story[]
     */
    public function getAll()
    {
        $deferred = new Deferred;

        \Amp\immediately(function () use ($deferred) {
            $storiesKeys = yield $this->redisClient->keys("story:*");

            $storiesRaw = yield $this->redisClient->mGet($storiesKeys);

            $stories = array_map(function ($storyRaw) {
                return $this->serializer->deserialize($storyRaw, Story::class, 'json');
            }, $storiesRaw);

            $deferred->succeed($stories);
        });

        return $deferred->promise();
    }

    /**
     * @param Story $story
     *
     * @return Promise|bool
     */
    public function save(Story $story)
    {
        $storyJson = $this->serializer->serialize($story, 'json');

        return $this->redisClient->setNx("story:{$story->number}", $storyJson);
    }
}
