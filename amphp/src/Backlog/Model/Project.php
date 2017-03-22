<?php declare(strict_types=1);
namespace Fedot\Backlog\Model;

use Fedot\DataMapper\Annotation\Field;
use Fedot\DataMapper\Annotation\Id;
use Fedot\DataMapper\Annotation\ReferenceMany;

class Project
{
    /**
     * @Id
     *
     * @var string
     */
    private $id;

    /**
     * @Field
     *
     * @var string
     */
    private $name;

    /**
     * @ReferenceMany(target=User::class)
     *
     * @var User[]
     */
    private $users;

    /**
     * @ReferenceMany(target=Story::class)
     *
     * @var Story[]
     */
    private $stories;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->users = [];
        $this->stories = [];
    }

    public function share(User $user): void
    {
        if (!in_array($user, $this->users, true)) {
            $this->users[] = $user;
            $user->addProject($this);
        }
    }

    public function createStory(Story $story): void
    {
        if (!in_array($story, $this->stories, true)) {
            array_unshift($this->stories, $story);
        }
    }

    public function removeStory(Story $story): void
    {
        $key = array_search($story, $this->stories, true);

        if (null !== $key) {
            unset($this->stories[$key]);
            $this->stories = array_values($this->stories);
        }
    }

    public function moveStoryBeforeStory($story, $positionStory)
    {
        $storyPosition = array_search($story, $this->stories, true);
        $positionElementPosition = array_search($positionStory, $this->stories, true);

        if (false === $storyPosition || false === $positionElementPosition) {
            throw new \LogicException('One of stories not found in project');
        }

        unset($this->stories[$storyPosition]);
        $this->stories = array_values($this->stories);

        $this->stories = array_merge(
            array_slice($this->stories, 0, $positionElementPosition + 1),
            [$story],
            array_slice($this->stories, $positionElementPosition + 1)
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return Story[]
     */
    public function getStories(): array
    {
        return $this->stories;
    }
}
