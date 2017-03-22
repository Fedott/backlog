<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Amp\Redis\Client;
use function Amp\Promise\wait;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Instantiator\Instantiator;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Model\User;
use Fedot\DataMapper\IdentityMap;
use Fedot\DataMapper\Metadata\Driver\AnnotationDriver;
use Fedot\DataMapper\Redis\ModelManager;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class IntegrationModelsTest extends TestCase
{
    /**
     * @var Client
     */
    public $redisClient;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        print `redis-server --daemonize yes --port 25325 --timeout 333 --pidfile /tmp/amp-redis.pid`;
    }

    public static function tearDownAfterClass()
    {
        $pid = @file_get_contents('/tmp/amp-redis.pid');
        @unlink('/tmp/amp-redis.pid');
        if (!empty($pid)) {
            print `kill $pid`;
        }
    }

    protected function getModelManager(): ModelManager
    {
        $this->redisClient = new Client('tcp://localhost:25325?database=7');
        $propertyAccessor = new PropertyAccessor();
        $modelManager = new ModelManager(
            new MetadataFactory(new AnnotationDriver(new AnnotationReader())), $this->redisClient,
            $propertyAccessor,
            new Instantiator()
        );
        return $modelManager;
    }

    public function testUserModel()
    {
        $modelManager = $this->getModelManager();

        $user = new User('testUser', 'hash');

        $this->assertTrue(wait($modelManager->persist($user)));

        /** @var User $loadedUser */
        $loadedUser = wait($modelManager->find(User::class, 'testUser'));

        $this->assertEquals($user->getUsername(), $loadedUser->getUsername());
        $this->assertEquals($user->getPasswordHash(), $loadedUser->getPasswordHash());
    }

    public function testProjectModel()
    {
        $modelManager = $this->getModelManager();

        $project = new Project('project-id-1', 'Project 1');

        $this->assertTrue(wait($modelManager->persist($project)));

        /** @var Project $loadedProject */
        $loadedProject = wait($modelManager->find(Project::class, 'project-id-1'));

        $this->assertEquals($project->getId(), $loadedProject->getId());
        $this->assertEquals($project->getName(), $loadedProject->getName());
        $this->assertEquals([], $loadedProject->getStories());
    }

    public function testProjectUserInteraction()
    {
        $modelManager = $this->getModelManager();

        $project = new Project('project-id-1', 'Project 1');
        $user = new User('testUser', 'hash');

        $user->addProject($project);

        $this->assertCount(1, $project->getUsers());
        $this->assertEquals($user, $project->getUsers()[0]);

        wait($modelManager->persist($project));
        wait($modelManager->persist($user));

        $identityMap = new IdentityMap();

        /** @var Project $loadedProject */
        $loadedProject = wait($modelManager->find(Project::class, $project->getId(), 1, $identityMap));
        /** @var User $loadedUser */
        $loadedUser = wait($modelManager->find(User::class, $user->getUsername(), 1, $identityMap));

        $this->assertEquals([$loadedProject], $loadedUser->getProjects());
        $this->assertEquals([$loadedUser], $loadedProject->getUsers());
    }

    public function testStoryProjectInteraction(){
        $modelManager = $this->getModelManager();

        $project = new Project('project-1', 'Project 1');
        $story1 = new Story('story-1', 'Story 1', 'Story 1 text', $project);
        $story2 = new Story('story-2', 'Story 2', 'Story 2 text', $project);
        $story3 = new Story('story-3', 'Story 3', 'Story 3 text', $project);

        $this->assertSame($project, $story1->getProject());
        $this->assertSame($project, $story2->getProject());
        $this->assertSame($project, $story3->getProject());

        $this->assertSame([
            $story3,
            $story2,
            $story1,
        ], $project->getStories());

        wait($modelManager->persist($story1));
        wait($modelManager->persist($story2));
        wait($modelManager->persist($story3));
        wait($modelManager->persist($project));

        $identityMap = new IdentityMap();

        /** @var Project $loadedProject */
        $loadedProject = wait($modelManager->find(Project::class, $project->getId(), 1, $identityMap));
        /** @var Story $loadedStory1 */
        $loadedStory1 = wait($modelManager->find(Story::class, $story1->getId(), 1, $identityMap));
        /** @var Story $loadedStory2 */
        $loadedStory2 = wait($modelManager->find(Story::class, $story2->getId(), 1, $identityMap));
        /** @var Story $loadedStory3 */
        $loadedStory3 = wait($modelManager->find(Story::class, $story3->getId(), 1, $identityMap));

        $this->assertEquals($story1->getId(), $loadedStory1->getId());
        $this->assertEquals($story1->getTitle(), $loadedStory1->getTitle());
        $this->assertEquals($story1->getText(), $loadedStory1->getText());
        $this->assertEquals($story1->getProject()->getId(), $loadedStory1->getProject()->getId());

        $this->assertEquals($story2->getId(), $loadedStory2->getId());
        $this->assertEquals($story2->getTitle(), $loadedStory2->getTitle());
        $this->assertEquals($story2->getText(), $loadedStory2->getText());
        $this->assertEquals($story2->getProject()->getId(), $loadedStory2->getProject()->getId());

        $this->assertEquals($story3->getId(), $loadedStory3->getId());
        $this->assertEquals($story3->getTitle(), $loadedStory3->getTitle());
        $this->assertEquals($story3->getText(), $loadedStory3->getText());
        $this->assertEquals($story3->getProject()->getId(), $loadedStory3->getProject()->getId());

        $this->assertEquals([
            $loadedStory3,
            $loadedStory2,
            $loadedStory1,
        ], $loadedProject->getStories());

        $loadedProject->removeStory($loadedStory2);

        $this->assertEquals([
            $loadedStory3,
            $loadedStory1,
        ], $loadedProject->getStories());
    }

    public function testProjectStoryMovingInteraction(){
        $modelManager = $this->getModelManager();

        $project = new Project('project-1', 'Project 1');
        $story1 = new Story('story-1', 'Story 1', 'Story 1 text', $project);
        $story2 = new Story('story-2', 'Story 2', 'Story 2 text', $project);
        $story3 = new Story('story-3', 'Story 3', 'Story 3 text', $project);

        $this->assertSame($project, $story1->getProject());
        $this->assertSame($project, $story2->getProject());
        $this->assertSame($project, $story3->getProject());

        $this->assertSame([
            $story3,
            $story2,
            $story1,
        ], $project->getStories());

        $project->moveStoryBeforeStory($story1, $story3);

        $this->assertSame([
            $story3,
            $story1,
            $story2,
        ], $project->getStories());

        wait($modelManager->persist($story1));
        wait($modelManager->persist($story2));
        wait($modelManager->persist($story3));
        wait($modelManager->persist($project));

        $identityMap = new IdentityMap();

        /** @var Project $loadedProject */
        $loadedProject = wait($modelManager->find(Project::class, $project->getId(), 1, $identityMap));
        /** @var Story $loadedStory1 */
        $loadedStory1 = wait($modelManager->find(Story::class, $story1->getId(), 1, $identityMap));
        /** @var Story $loadedStory2 */
        $loadedStory2 = wait($modelManager->find(Story::class, $story2->getId(), 1, $identityMap));
        /** @var Story $loadedStory3 */
        $loadedStory3 = wait($modelManager->find(Story::class, $story3->getId(), 1, $identityMap));

        $this->assertEquals([
            $loadedStory3,
            $loadedStory1,
            $loadedStory2,
        ], $loadedProject->getStories());
    }

    public function testStoryRequirementInteraction()
    {
        $project = new Project('project-id', 'project name');
        $story = new Story('story-id', 'story title', 'story text', $project);
        $requirement1 = new Requirement('requirement-id1', 'req text 1', $story);
        $requirement2 = new Requirement('requirement-id2', 'req text 2', $story);
        $requirement3 = new Requirement('requirement-id3', 'req text 3', $story);

        $this->assertSame([
            $requirement1,
            $requirement2,
            $requirement3,
        ], $story->getRequirements());

        $this->assertSame($story, $requirement1->getStory());
        $this->assertSame($story, $requirement2->getStory());
        $this->assertSame($story, $requirement3->getStory());

        $identityMap = new IdentityMap();

        $modelManager = $this->getModelManager();

        wait($modelManager->persist($project));
        wait($modelManager->persist($story));
        wait($modelManager->persist($requirement1));
        wait($modelManager->persist($requirement2));
        wait($modelManager->persist($requirement3));

        /** @var Story $loadedStory */
        $loadedStory = wait($modelManager->find(Story::class, 'story-id', 1, $identityMap));
        /** @var Requirement $loadedReq1 */
        $loadedReq1 = wait($modelManager->find(Requirement::class, 'requirement-id1', 1, $identityMap));
        /** @var Requirement $loadedReq2 */
        $loadedReq2 = wait($modelManager->find(Requirement::class, 'requirement-id2', 1, $identityMap));
        /** @var Requirement $loadedReq3 */
        $loadedReq3 = wait($modelManager->find(Requirement::class, 'requirement-id3', 1, $identityMap));

        $this->assertSame([
            $loadedReq1,
            $loadedReq2,
            $loadedReq3,
        ], $loadedStory->getRequirements());

        $this->assertSame($loadedStory, $loadedReq1->getStory());
        $this->assertSame($loadedStory, $loadedReq2->getStory());
        $this->assertSame($loadedStory, $loadedReq3->getStory());

        $this->assertSame($requirement1->getId(), $loadedReq1->getId());
        $this->assertSame($requirement1->getText(), $loadedReq1->getText());

        $this->assertSame($requirement2->getId(), $loadedReq2->getId());
        $this->assertSame($requirement2->getText(), $loadedReq2->getText());

        $this->assertSame($requirement3->getId(), $loadedReq3->getId());
        $this->assertSame($requirement3->getText(), $loadedReq3->getText());
    }
}
