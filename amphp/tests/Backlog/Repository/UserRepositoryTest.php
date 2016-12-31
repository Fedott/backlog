<?php declare(strict_types = 1);
namespace Tests\Fedot\Backlog\Repository;

use Amp\Success;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\UserRepository;
use Fedot\DataStorage\Redis\FetchManager;
use Fedot\DataStorage\Redis\PersistManager;
use Tests\Fedot\Backlog\BaseTestCase;

class UserRepositoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $fetchManagerMock = $this->createMock(FetchManager::class);
        $persistManagerMock = $this->createMock(PersistManager::class);

        $repository = new UserRepository($fetchManagerMock, $persistManagerMock);

        $user = new User();
        $user->username = 'testUser';
        $user->password = 'testPassword';

        $persistManagerMock->expects($this->once())
            ->method('persist')
            ->with($user, false)
            ->willReturn(new Success(true))
        ;

        $actualResult = \Amp\wait($repository->create($user));

        $this->assertTrue($actualResult);
    }

    public function testCreateNegative()
    {
        $fetchManagerMock = $this->createMock(FetchManager::class);
        $persistManagerMock = $this->createMock(PersistManager::class);

        $repository = new UserRepository($fetchManagerMock, $persistManagerMock);

        $user = new User();
        $user->username = 'testUser';
        $user->password = 'testPassword';

        $persistManagerMock->expects($this->once())
            ->method('persist')
            ->with($user, false)
            ->willReturn(new Success(false))
        ;

        $actualResult = \Amp\wait($repository->create($user));

        $this->assertFalse($actualResult);
    }

    public function testGet()
    {
        $fetchManagerMock = $this->createMock(FetchManager::class);
        $persistManagerMock = $this->createMock(PersistManager::class);

        $repository = new UserRepository($fetchManagerMock, $persistManagerMock);

        $user = new User();
        $user->username = 'testUser';
        $user->password = 'testPassword';

        $fetchManagerMock->expects($this->once())
            ->method('fetchById')
            ->with(User::class, 'testUser')
            ->willReturn(new Success($user))
        ;

        $actualUser = \Amp\wait($repository->get('testUser'));

        $this->assertEquals($user, $actualUser);
    }
}
