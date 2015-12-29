<?php
namespace BacklogBundle\Controller;

use BacklogBundle\Document\Story\StoriesRepository;
use BacklogBundle\Document\Story\Story;
use BacklogBundle\Document\Story\StoryType;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class StoriesController extends FOSRestController
{
    /**
     * @return StoriesRepository
     */
    protected function getStoriesRepository()
    {
        return $this->container->get('backlog.repository.stories');
    }

    /**
     * @return array
     */
    public function getStoriesAction()
    {
        return $this->getStoriesRepository()->findAll();
    }

    /**
     * @param Story $story
     */
    public function getStoryAction(Story $story)
    {

    }

    /**
     * @View(statusCode=201)
     *
     * @param Request $request
     * @return Story|\Symfony\Component\Form\Form
     */
    public function postStoriesAction(Request $request)
    {
        $story = new Story();
        $form = $this->createForm(StoryType::class, $story);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            $this->getStoriesRepository()->getDocumentManager()->persist($story);
            $this->getStoriesRepository()->getDocumentManager()->flush();

            return $story;
        } else {
            return $form;
        }
    }
}
