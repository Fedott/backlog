<?php
namespace BacklogBundle\Document\Requirement;

use BacklogBundle\Document\Story\Story;
use Doctrine\MongoDB\CursorInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\LockMode;

/**
 * @method Story find($id, $lockMode = LockMode::NONE, $lockVersion = null)
 */
class RequirementsRepository extends DocumentRepository
{
    /**
     * @param Story $story
     * @return CursorInterface
     */
    public function findByStory(Story $story)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->field('story')->references($story);

        return $queryBuilder->getQuery()->execute();
    }
}
