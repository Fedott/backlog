<?php
namespace BacklogBundle\Document\Requirement;

use BacklogBundle\Document\Story\Story;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RequirementTest extends KernelTestCase
{
    public function testRequirement()
    {
        static::bootKernel();

        $requirementRepository = static::$kernel->getContainer()->get('backlog.repository.requirements');
        $documentManager = $requirementRepository->getDocumentManager();

        $requirement = new Requirement();
        $requirement->setName('Design');

        $documentManager->persist($requirement);
        $documentManager->flush();

        $documentManager->clear();

        $loadedRequirement = $requirementRepository->find($requirement->getId());

        $this->assertEquals($requirement->getId(), $loadedRequirement->getId());
    }

    public function testWithStory()
    {
        static::bootKernel();

        $requirementRepository = static::$kernel->getContainer()->get('backlog.repository.requirements');
        $documentManager = $requirementRepository->getDocumentManager();

        $story = new Story();
        $story->setText("Story text");

        $requirement = new Requirement();
        $requirement->setName('design');
        $requirement->setStory($story);

        $documentManager->persist($story);
        $documentManager->flush();

        $documentManager->persist($requirement);
        $documentManager->flush();

        $documentManager->clear();

        /** @var Requirement $loadedRequirement */
        $loadedRequirement = $requirementRepository->find($requirement->getId());

        $this->assertEquals($requirement->getId(), $loadedRequirement->getId());
        $this->assertEquals($story->getId(), $loadedRequirement->getStory()->getId());
    }

    public function testRepositoryFindByStore()
    {
        static::bootKernel();

        $requirementRepository = static::$kernel->getContainer()->get('backlog.repository.requirements');
        $documentManager = $requirementRepository->getDocumentManager();

        $story = new Story();
        $story->setText("Story text");

        $requirement = new Requirement();
        $requirement->setName('design');
        $requirement->setStory($story);

        $documentManager->persist($story);
        $documentManager->flush();

        $documentManager->persist($requirement);
        $documentManager->flush();

        $foundRequirements = $requirementRepository->findByStory($story);
        $this->assertCount(1, $foundRequirements);
        $this->assertEquals($requirement, $foundRequirements->getNext());
    }
}
