<?php
namespace BacklogBundle\Document\Requirement;

use BacklogBundle\Document\Story\Story;
use Doctrine\ODM\MongoDB\DocumentRepository;

class RequirementsRepository extends DocumentRepository
{
    /**
     * @param Story $story
     * @return array
     */
    public function findByStory(Story $story)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->field('story')->references($story);

        return $queryBuilder->getQuery()->execute();
    }
}
