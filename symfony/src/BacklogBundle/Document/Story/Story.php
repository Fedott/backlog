<?php
namespace BacklogBundle\Document\Story;

use BacklogBundle\Document\Requirement\Requirement;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ODM\ReferenceMany(targetDocument="BacklogBundle\Document\Requirement\Requirement", cascade="all")
     *
     * @var Requirement[]
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
     * @param Requirement $requirement
     * @return $this
     */
    public function addRequirement(Requirement $requirement)
    {
        $this->requirements[] = $requirement;

        return $this;
    }

    /**
     * @return Requirement[]
     */
    public function getRequirements()
    {
        return $this->requirements;
    }
}
