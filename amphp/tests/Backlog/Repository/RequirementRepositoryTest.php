<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Repository;

use Amp\Success;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\RequirementRepository;
use Fedot\DataMapper\FetchManagerInterface;
use Fedot\DataMapper\ModelManagerInterface;
use Fedot\DataMapper\PersistManagerInterface;
use Fedot\DataMapper\RelationshipManagerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\BaseTestCase;
use function Amp\Promise\wait;

class RequirementRepositoryTest extends BaseTestCase
{
    /**
     * @var ModelManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelManagerMock;

    /**
     * @var RequirementRepository
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->modelManagerMock = $this->createMock(ModelManagerInterface::class);

        $this->repository = new RequirementRepository(
            $this->modelManagerMock
        );
    }

    public function testCreate()
    {
        $story = $this->createMock(Story::class);
        $requirement = $this->createMock(Requirement::class);

        $this->modelManagerMock->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive(
                [$requirement],
                [$story]
            )
            ->willReturn(new Success(true))
        ;

        $result = wait($this->repository->create($story, $requirement));

        $this->assertTrue($result);
    }

    public function testSave()
    {
        $requirement = $this->createMock(Requirement::class);

        $this->modelManagerMock->expects($this->once())
            ->method('persist')
            ->with($requirement)
            ->willReturn(new Success(true))
        ;

        $result = wait($this->repository->save($requirement));

        $this->assertTrue($result);
    }

    public function testGetAllByStory()
    {
        $story = $this->createMock(Story::class);
        $requirement = $this->createMock(Requirement::class);
        $requirement2 = $this->createMock(Requirement::class);

        $story->expects($this->once())
            ->method('getRequirements')
            ->willReturn([
                $requirement,
                $requirement2
            ])
        ;

        $result = wait($this->repository->getAllByStory($story));

        $this->assertEquals([
            $requirement,
            $requirement2,
        ], $result);
    }

    public function testGetPositive()
    {
        $requirement = $this->createMock(Requirement::class);

        $this->modelManagerMock->expects($this->once())
            ->method('find')
            ->with(Requirement::class, 'id')
            ->willReturn(new Success($requirement))
        ;

        $result = wait($this->repository->get('id'));
        $this->assertEquals($requirement, $result);
    }

    public function testGetNegative()
    {
        $this->modelManagerMock->expects($this->once())
            ->method('find')
            ->with(Requirement::class, 'id')
            ->willReturn(new Success(null))
        ;

        $result = wait($this->repository->get('id'));
        $this->assertNull($result);
    }
}
