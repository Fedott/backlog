<?php declare(strict_types = 1);
namespace Fedot\Backlog\Repository;

use Amp\Deferred;
use Amp\Promise;
use Amp\Redis\Client;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\User;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectRepository
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
    protected $keyPrefix = "projects";

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
     * @param User $user
     * @param Project $project
     *
     * @return Promise
     * @yield bool
     */
    public function create(User $user, Project $project): Promise
    {
        $promisor = new Deferred();

        \Amp\immediately(function () use ($promisor, $user, $project) {
            $json = $this->serializer->serialize($project, 'json');

            $projectKey = $this->getKeyForId($project->id);

            yield $this->redisClient->set($projectKey, $json);

            yield $this->redisClient->lPush(
                $this->getKeyIndexForUser($user),
                $projectKey
            );

            $promisor->succeed(true);
        });

        return $promisor->promise();
    }

    private function getKeyForId(string $id): string
    {
        return "{$this->keyPrefix}:entities:{$id}";
    }

    private function getKeyIndexForUser(User $user): string
    {
        return "{$this->keyPrefix}:index:by-user:{$user->username}";
    }
}
