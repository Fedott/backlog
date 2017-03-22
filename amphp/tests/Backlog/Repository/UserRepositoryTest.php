<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Repository;

use Amp\Success;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\UserRepository;
use Fedot\DataMapper\Redis\FetchManager;
use Fedot\DataMapper\Redis\ModelManager;
use Fedot\DataMapper\Redis\PersistManager;
use Tests\Fedot\Backlog\BaseTestCase;

class UserRepositoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $modelManagerMock = $this->createMock(ModelManager::class);

        $repository = new UserRepository($modelManagerMock);

        $user = new User('testUser', 'testPassword');

        $modelManagerMock->expects($this->once())
            ->method('find')
            ->with(User::class, 'testUser')
            ->willReturn(new Success(null))
        ;

        $modelManagerMock->expects($this->once())
            ->method('persist')
            ->with($user)
            ->willReturn(new Success(true))
        ;

        $actualResult = \Amp\wait($repository->create($user));

        $this->assertTrue($actualResult);
    }

    public function testCreateNegative()
    {
        $modelManagerMock = $this->createMock(ModelManager::class);

        $repository = new UserRepository($modelManagerMock);

        $user = new User('testUser', 'testPassword');
        $userLoaded = new User('testUser', 'testPassword');

        $modelManagerMock->expects($this->once())
            ->method('find')
            ->with(User::class, 'testUser')
            ->willReturn(new Success($userLoaded))
        ;

        $modelManagerMock->expects($this->never())
            ->method('persist')
        ;

        $actualResult = \Amp\wait($repository->create($user));

        $this->assertFalse($actualResult);
    }

    public function testGet()
    {
        $modelManagerMock = $this->createMock(ModelManager::class);

        $repository = new UserRepository($modelManagerMock);

        $user = new User('testUser', 'testPassword');

        $modelManagerMock->expects($this->once())
            ->method('find')
            ->with(User::class, 'testUser')
            ->willReturn(new Success($user))
        ;

        $actualUser = \Amp\wait($repository->get('testUser'));

        $this->assertEquals($user, $actualUser);
    }
}
