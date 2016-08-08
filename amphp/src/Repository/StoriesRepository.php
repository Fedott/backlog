<?php declare(strict_types=1);
namespace Fedot\Backlog\Repository;

use Amp\Promise;
use Amp\Deferred;
use Amp\Redis\Client;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Model\User;
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
     * @param User $user
     *
     * @return string
     */
    protected function getKeyForStoriesSortDefault(User $user)
    {
        return "user:{$user->username}:stories:sorted:default";
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
            $storiesKeys = yield $this->redisClient->lRange("stories:sort:default", 0, -1);

            if (empty($storiesKeys)) {
                $deferred->succeed([]);

                return;
            }

            $storiesRaw = yield $this->redisClient->mGet($storiesKeys);

            $stories = array_map(function ($storyRaw) {
                return $this->serializer->deserialize($storyRaw, Story::class, 'json');
            }, $storiesRaw);

            $deferred->succeed($stories);
        });

        return $deferred->promise();
    }

    /**
     * @param User $user
     * @param Story $story
     *
     * @return Promise|bool
     */
    public function create(User $user, Story $story): Promise
    {
        $deferred = new Deferred();

        \Amp\immediately(function () use ($deferred, $story, $user) {
            $storyJson = $this->serializeStoryToJson($story);

            $created = yield $this->redisClient->setNx($this->getKeyForStory($story->id), $storyJson);

            if ($created) {
                yield $this->redisClient->lPush(
                    $this->getKeyForStoriesSortDefault($user),
                    $this->getKeyForStory($story->id)
                );

                $deferred->succeed(true);
            } else {
                $deferred->succeed(false);
            }
        });

        return $deferred->promise();
    }

    /**
     * @param User  $user
     * @param Story $story
     *
     * @return Promise|bool
     */
    public function save(User $user, Story $story): Promise
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
        $deferred = new Deferred();

        \Amp\immediately(function() use ($deferred, $storyId) {
            yield $this->redisClient->lRem("stories:sort:default", $this->getKeyForStory($storyId), 1);

            yield $this->redisClient->del($this->getKeyForStory($storyId));

            $deferred->succeed(true);
        });

        return $deferred->promise();
    }

    /**
     * @param string $storyId
     * @param string $afterStoryId
     *
     * @return Promise|bool
     */
    public function move(string $storyId, string $afterStoryId)
    {
        $deferred = new Deferred();

        \Amp\immediately(function () use ($deferred, $storyId, $afterStoryId) {
            $storyKey = $this->getKeyForStory($storyId);

            yield $this->redisClient->lRem("stories:sort:default", $storyKey, 1);
            $insertResult = yield $this->redisClient->lInsert(
                "stories:sort:default",
                "before",
                $this->getKeyForStory($afterStoryId),
                $storyKey
            );

            if ($insertResult !== -1) {
                $deferred->succeed(true);
            } else {
                yield $this->redisClient->lPush("stories:sort:default", $storyKey);

                $deferred->succeed(false);
            }
        });

        return $deferred->promise();
    }
}
