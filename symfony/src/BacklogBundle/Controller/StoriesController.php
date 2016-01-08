<?php
namespace BacklogBundle\Controller;

use BacklogBundle\Document\Story\StoriesRepository;
use BacklogBundle\Document\Story\Story;
use BacklogBundle\Document\Story\StoryType;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
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
     * @QueryParam(name="completed", requirements="(true|false|all)", strict=true, default="all")
     *
     * @param ParamFetcher $paramFetcher
     * @return array
     */
    public function getStoriesAction(ParamFetcher $paramFetcher)
    {
        $completed = $paramFetcher->get('completed');
        if ($completed !== 'all') {
            return $this->getStoriesRepository()->findByCompleted($completed === 'true');
        } else {
            return $this->getStoriesRepository()->findAll();
        }
    }

    /**
     * @param Story $story
     * @return Story
     */
    public function getStoryAction(Story $story)
    {
        return $story;
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

    /**
     * @param Story $story
     * @param Request $request
     * @return Story|\Symfony\Component\Form\Form
     */
    public function putStoryAction(Story $story, Request $request)
    {
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
