<?php
namespace BacklogBundle\Document\Story;

use BacklogBundle\Document\Requirement\Requirement;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StoryTest extends KernelTestCase
{
    public function testStoryCreate()
    {
        static::bootKernel();

        $repository = static::$kernel->getContainer()->get('backlog.repository.stories');
        $dm = $repository->getDocumentManager();

        $story = new Story();
        $story->setText('Text story test');

        $dm->persist($story);
        $dm->flush();
        $dm->clear();

        $loadedStory = $repository->find($story->getId());

        $this->assertEquals($story->getId(), $loadedStory->getId());
        $this->assertNotEquals($story, $loadedStory);
    }

    public function testStoryWithRequirements()
    {
        static::bootKernel();

        $repository = static::$kernel->getContainer()->get('backlog.repository.stories');
        $dm = $repository->getDocumentManager();

        $story = new Story();
        $story->setText('Text');
        $requirement1 = new Requirement();
        $requirement1->setName('design');
        $requirement2 = new Requirement();
        $requirement2->setName('markup');

        $story->addRequirement($requirement1)->addRequirement($requirement2);

        $dm->persist($story);
        $dm->flush();
        $dm->clear();

        $loadedStory = $repository->find($story->getId());

        $this->assertEquals($story->getId(), $loadedStory->getId());
        $this->assertNotEquals($story, $loadedStory);
        $this->assertEquals($story->getRequirements()[0]->getId(), $loadedStory->getRequirements()[0]->getId());
        $this->assertEquals($story->getRequirements()[1]->getId(), $loadedStory->getRequirements()[1]->getId());
    }

    public function testStoryCompleted()
    {
        static::bootKernel();

        $repository = static::$kernel->getContainer()->get('backlog.repository.stories');
        $dm = $repository->getDocumentManager();


        $story = new Story();
        $story->setText('Text');
        $story->setCompleted(true);

        $dm->persist($story);
        $dm->flush();
        $dm->clear();

        $loadedStory = $repository->find($story->getId());
        $this->assertEquals('Text', $loadedStory->getText());
        $this->assertEquals(true, $loadedStory->isCompleted());
    }
}
