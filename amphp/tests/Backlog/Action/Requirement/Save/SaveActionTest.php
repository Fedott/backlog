<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Action\Requirement\Save;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Instantiator\Instantiator;
use Fedot\Backlog\Action\ActionInterface;
use Fedot\Backlog\Action\Requirement\Save\SaveAction;
use Fedot\Backlog\Action\Requirement\Save\SavePayload;
use Fedot\Backlog\Model\Project;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\RequirementRepository;
use Fedot\Backlog\WebSocket\Response;
use Fedot\DataMapper\Memory\ModelManager;
use Fedot\DataMapper\Metadata\Driver\AnnotationDriver;
use Fedot\DataMapper\ModelManagerInterface;
use Metadata\MetadataFactory;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tests\Fedot\Backlog\ActionTestCase;
use function Amp\Promise\all;
use function Amp\Promise\wait;

class SaveActionTest extends ActionTestCase
{
    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var RequirementRepository
     */
    protected $requirementRepository;

    protected function getProcessorInstance(): ActionInterface
    {
        $this->modelManager = new ModelManager(
            new MetadataFactory(
                new AnnotationDriver(new AnnotationReader())
            ),
            new PropertyAccessor(),
            new Instantiator(),
            2
        );
        $this->requirementRepository = new RequirementRepository(
            $this->modelManager
        );

        return new SaveAction(
            $this->requirementRepository
        );
    }

    protected function getExpectedValidRequestType(): string
    {
        return 'story/requirements/save';
    }

    protected function getExpectedPayloadType(): ?string
    {
        return SavePayload::class;
    }

    public function testPositive()
    {
        $project = new Project('project1', 'Test Project');
        $story = new Story('storyId1', 'Test Story', 'Test story text', $project);
        $requirement = new Requirement('req1', 'Test requirement', $story);

        all([
            $this->modelManager->persist($project),
            $this->modelManager->persist($story),
            $this->modelManager->persist($requirement),
        ]);

        $payload = new SavePayload();
        $payload->id = 'req1';
        $payload->text = 'New test requirement text';

        $request = $this->makeRequest(1, 2, 'story/requirements/save', $payload);

        /** @var Response $response */
        $response = wait($this->action->process($request, $this->makeResponse($request)));

        $this->assertResponseBasic($response, 1, 2, 'requirement-saved');
        $actualPayload = $response->getPayload();
        $this->assertEquals([
            'id' => 'req1',
            'text' => 'New test requirement text',
            'completed' => false,
        ], $actualPayload);

        /** @var Requirement $actualRequirement */
        $actualRequirement = wait($this->requirementRepository->get('req1'));
        $this->assertEquals('req1', $actualRequirement->getId());
        $this->assertEquals('New test requirement text', $actualRequirement->getText());
        $this->assertEquals(false, $actualRequirement->isCompleted());
    }
}
