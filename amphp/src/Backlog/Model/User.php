<?php declare(strict_types=1);

namespace Fedot\Backlog\Model;

use Fedot\DataMapper\Annotation\Field;
use Fedot\DataMapper\Annotation\Id;
use Fedot\DataMapper\Annotation\ReferenceMany;

class User
{
    /**
     * @Id
     *
     * @var string
     */
    private $username;

    /**
     * @Field
     *
     * @var string
     */
    private $passwordHash;

    /**
     * @ReferenceMany(target=Project::class)
     *
     * @var Project[]
     */
    private $projects;

    public function __construct(string $username, string $passwordHash)
    {
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->projects = [];
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function addProject(Project $project): void
    {
        if (!in_array($project, $this->projects, true)) {
            $this->projects[] = $project;
            $project->share($this);
        }
    }

    /**
     * @return Project[]
     */
    public function getProjects(): array
    {
        return $this->projects;
    }
}
