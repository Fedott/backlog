<?php
namespace BacklogBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

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
     * @var string
     */
    protected $text;
}
