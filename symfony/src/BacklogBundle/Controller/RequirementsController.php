<?php
namespace BacklogBundle\Controller;

use BacklogBundle\Document\DocumentCollection;
use BacklogBundle\Document\Requirement\RequirementsRepository;
use BacklogBundle\Document\Story\Story;
use FOS\RestBundle\Controller\FOSRestController;

class RequirementsController extends FOSRestController
{
    /**
     * @return RequirementsRepository
     */
    protected function getRequirementsRepository()
    {
        return $this->container->get('backlog.repository.requirements');
    }

    /**
     * @param Story $story
     * @return array
     */
    public function getStoryRequirementsAction(Story $story)
    {
        $requirements = $this->getRequirementsRepository()
            ->findByStory($story);

        return new DocumentCollection($requirements);
    }
}
