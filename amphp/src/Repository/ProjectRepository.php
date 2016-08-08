<?php declare(strict_types = 1);
namespace Fedot\Backlog\Repository;

use Amp\Promise;
use Amp\Redis\Client;
use Fedot\Backlog\Model\Project;
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
    protected $keyPrefix = "project:";

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
     * @param Project $project
     *
     * @return Promise
     * @yield Project
     */
    public function create(Project $project): Promise
    {

    }
}
