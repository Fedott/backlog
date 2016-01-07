<?php
namespace BacklogBundle\Document\Story;

use BacklogBundle\Document\Requirement\Requirement;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Document(repositoryClass="StoriesRepository")
 */
class Story
{
    /**
     * @ODM\Id()
     *
     * @var string
     */
    protected $id;

    /**
     * @ODM\String()
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $text;

    /**
     * @ODM\Boolean()
     *
     * @var bool
     */
    protected $completed = false;

    /**
     * @ODM\ReferenceMany(
     *     targetDocument="BacklogBundle\Document\Requirement\Requirement",
     *     cascade="all",
     *     mappedBy="story"
     * )
     * @Serializer\MaxDepth(depth=2);
     *
     * @var Requirement[]|PersistentCollection
     */
    protected $requirements = [];

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Story
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Story
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @param boolean $completed
     * @return Story
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * @param Requirement $requirement
     * @return $this
     */
    public function addRequirement(Requirement $requirement)
    {
        $requirement->setStory($this);
        $this->requirements[] = $requirement;

        return $this;
    }

    /**
     * @return Requirement[]|PersistentCollection
     */
    public function getRequirements()
    {
        return $this->requirements;
    }
}
