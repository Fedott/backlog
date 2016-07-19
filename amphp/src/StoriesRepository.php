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
     * @param Story $story
     *
     * @return string
     */
    protected function serializeStoryToJson(Story $story): string
    {
        return $this->serializer->serialize($story, 'json');
    }

    /**
     * @return Promise|Story[]
     */
    public function getAll(): Promise
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
    public function create(Story $story): Promise
    {
        $storyJson = $this->serializeStoryToJson($story);

        return $this->redisClient->setNx($this->getKeyForStory($story->id), $storyJson);
    }

    /**
     * @param Story $story
     *
     * @return Promise|bool
     */
    public function save(Story $story): Promise
    {
        $storyJson = $this->serializeStoryToJson($story);

        return $this->redisClient->set($this->getKeyForStory($story->id), $storyJson);
    }

    /**
     * @param string $storyId
     *
     * @return Promise|bool
     */
    public function delete(string $storyId): Promise
    {
        return $this->redisClient->del($this->getKeyForStory($storyId));
    }
}
