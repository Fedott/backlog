<?php
namespace Fedot\Backlog;

use Amp\Promise;
use Amp\Deferred;
use Amp\Redis\Client;
use Fedot\Backlog\Model\Story;
use Symfony\Component\Serializer\SerializerInterface;

class StoriesRepository
{
    /**
     * @var Client
     */
    protected $redisClient;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $storyKeyPrefix = "story:";

    /**
     * StoriesRepository constructor.
     *
     * @param Client              $redisClient
     * @param SerializerInterface $serializer
     */
    public function __construct(Client $redisClient, SerializerInterface $serializer)
    {
        $this->redisClient = $redisClient;
        $this->serializer  = $serializer;
    }

    /**
     * @param string $storyId
     *
     * @return string
     */
    protected function getKeyForStory(string $storyId)
    {
        return "{$this->storyKeyPrefix}{$storyId}";
    }

    /**
     * @return Promise|Story[]
     */
    public function getAll()
    {
        $deferred = new Deferred;

        \Amp\immediately(function () use ($deferred) {
            $storiesKeys = yield $this->redisClient->keys("{$this->storyKeyPrefix}*");

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

        return $this->redisClient->setNx($this->getKeyForStory($story->id), $storyJson);
    }

    /**
     * @param string $storyId
     *
     * @return Promise|bool
     */
    public function delete(string $storyId)
    {
        return $this->redisClient->del($this->getKeyForStory($storyId));
    }
}
