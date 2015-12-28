<?php
namespace BacklogBundle\Controller;

use BacklogBundle\Document\StoriesRepository;

class StoriesController
{
    /**
     * @var StoriesRepository
     */
    protected $storiesRepository;

    /**
     * @param StoriesRepository $storiesRepository
     */
    public function __construct(StoriesRepository $storiesRepository)
    {
        $this->storiesRepository = $storiesRepository;
    }

    public function getStoriesAction()
    {
        return [];
    }
}
