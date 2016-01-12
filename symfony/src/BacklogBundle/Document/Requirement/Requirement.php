<?php
namespace BacklogBundle\Document\Requirement;

use BacklogBundle\Document\Story\Story;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(repositoryClass="RequirementsRepository")
 */
class Requirement
{
    /**
     * @ODM\Id()
     *
     * @var string
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="100");
     *
     * @var string
     */
    protected $name;

    /**
     * @ODM\Boolean()
     *
     * @var bool
     */
    protected $isComplete = false;

    /**
     * @ODM\ReferenceOne(targetDocument="BacklogBundle\Document\Story\Story", inversedBy="requirements", cascade="all")
     * @Serializer\Exclude()
     *
     * @var Story
     */
    protected $story;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Requirement
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Requirement
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsComplete()
    {
        return $this->isComplete;
    }

    /**
     * @param mixed $isComplete
     * @return Requirement
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;

        return $this;
    }

    /**
     * @return Story
     */
    public function getStory()
    {
        return $this->story;
    }

    /**
     * @param Story $story
     * @return $this
     */
    public function setStory($story)
    {
        $this->story = $story;

        return $this;
    }
}
