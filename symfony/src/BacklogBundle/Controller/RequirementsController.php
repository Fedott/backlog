<?php
namespace BacklogBundle\Controller;

use BacklogBundle\Document\DocumentCollection;
use BacklogBundle\Document\Requirement\Requirement;
use BacklogBundle\Document\Requirement\RequirementsRepository;
use BacklogBundle\Document\Requirement\RequirementType;
use BacklogBundle\Document\Story\Story;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

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
        return $this->getRequirementsRepository()
            ->findByStory($story);
    }

    /**
     * @View(statusCode=201)
     *
     * @param Story $story
     * @param Request $request
     * @return Requirement|\Symfony\Component\Form\Form
     */
    public function postStoryRequirementAction(Story $story, Request $request)
    {
        $requirement = new Requirement();
        $requirement->setStory($story);

        $form = $this->createForm(RequirementType::class, $requirement);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            $this->getRequirementsRepository()->getDocumentManager()->persist($requirement);
            $this->getRequirementsRepository()->getDocumentManager()->flush();

            return $requirement;
        } else {
            return $form;
        }
    }

    /**
     * @param Story       $story
     * @param Requirement $requirement
     * @param Request     $request
     *
     * @return Requirement|\Symfony\Component\Form\Form
     */
    public function putStoryRequirementAction(Story $story, Requirement $requirement, Request $request)
    {
        $form = $this->createForm(RequirementType::class, $requirement);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            $this->getRequirementsRepository()->getDocumentManager()->persist($requirement);
            $this->getRequirementsRepository()->getDocumentManager()->flush();

            return $requirement;
        } else {
            return $form;
        }
    }
}
