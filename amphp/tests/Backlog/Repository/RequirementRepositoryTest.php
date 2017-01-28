<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog\Repository;

use Amp\Success;
use function Amp\wait;
use Fedot\Backlog\Model\Requirement;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Repository\RequirementRepository;
use Fedot\DataStorage\FetchManagerInterface;
use Fedot\DataStorage\PersistManagerInterface;
use Fedot\DataStorage\RelationshipManagerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\BaseTestCase;

class RequirementRepositoryTest extends BaseTestCase
{
    /**
     * @var RelationshipManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationshipManagerInterfaceMock;

    /**
     * @var PersistManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistManagerInterfaceMock;

    /**
     * @var FetchManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchManagerInterfaceMock;

    /**
     * @var RequirementRepository
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->relationshipManagerInterfaceMock = $this->createMock(RelationshipManagerInterface::class);
        $this->persistManagerInterfaceMock = $this->createMock(PersistManagerInterface::class);
        $this->fetchManagerInterfaceMock = $this->createMock(FetchManagerInterface::class);

        $this->repository = new RequirementRepository(
            $this->persistManagerInterfaceMock,
            $this->fetchManagerInterfaceMock,
            $this->relationshipManagerInterfaceMock
        );
    }

    public function testCreate()
    {
        $story = new Story();
        $requirement = new Requirement('id', 'text');

        $this->persistManagerInterfaceMock->expects($this->once())
            ->method('persist')
            ->with($requirement)
            ->willReturn(new Success(true))
        ;
        $this->relationshipManagerInterfaceMock->expects($this->once())
            ->method('addOneToMany')
            ->with($story, $requirement)
            ->willReturn(new Success(true))
        ;

        $result = wait($this->repository->create($story, $requirement));

        $this->assertTrue($result);
    }

    public function testSave()
    {
        $requirement = new Requirement('id', 'text');

        $this->persistManagerInterfaceMock->expects($this->once())
            ->method('persist')
            ->with($requirement, true)
            ->willReturn(new Success(true))
        ;

        $result = wait($this->repository->save($requirement));

        $this->assertTrue($result);
    }
}
