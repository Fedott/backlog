<?php
namespace BacklogBundle\Controller;

use BacklogBundle\Document\StoriesRepository;
use FOS\RestBundle\Controller\FOSRestController;

class StoriesController extends FOSRestController
{
    /**
     * @return StoriesRepository
     */
    protected function getStoriesRepository()
    {
        return $this->container->get('backlog.repository.stories');
    }

    public function getStoriesAction()
    {
        return $this->getStoriesRepository()->findAll();
    }
}
