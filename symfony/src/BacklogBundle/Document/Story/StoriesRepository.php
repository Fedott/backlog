<?php
namespace BacklogBundle\Document\Story;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\LockMode;

/**
 * @method Story find($id, $lockMode = LockMode::NONE, $lockVersion = null)
 */
class StoriesRepository extends DocumentRepository
{
}
