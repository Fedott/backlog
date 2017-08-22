<?php declare(strict_types=1);
namespace Tests\Fedot\Backlog;

use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use Fedot\Backlog\Repository\UserRepository;
use PHPUnit_Framework_MockObject_MockObject;

class AuthenticationServiceTest extends BaseTestCase
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected $redisClientMock;

    /**
     * @return AuthenticationService
     */
    protected function getServiceInstance()
    {
        $this->redisClientMock = $this->createMock(Client::class);
        $this->userRepository = new UserRepository($this->modelManager);

        return new AuthenticationService($this->redisClientMock, $this->userRepository);
    }

    public function testAuthByUsernamePassword()
    {
        $service = $this->getServiceInstance();

        $user = new User(
            'testUser',
            '$2y$10$kEYXDhRhNmS1mk226hurv.i23tmnFXuqa1LCMG7UoyhZ3nF/PK7a2'
        );

        $this->modelManager->persist($user);

        $this->redisClientMock->expects($this->once())
            ->method('set')
            ->with(
                $this->matchesRegularExpression('/auth:token:[a-zA-Z0-9]{32}/'),
                $this->equalTo('testUser'),
                $this->equalTo(864000), // 10 days
                $this->equalTo(false),
                $this->equalTo('NX')
            )
            ->willReturn(new Success(true))
        ;

        /** @var User $actualUser */
        list($actualUser, $actualToken) = \Amp\Promise\wait(
            $service->authByUsernamePassword('testUser', 'testPassword')
        );

        $this->assertInstanceOf(User::class, $actualUser);
        $this->assertEquals($actualUser->getUsername(), 'testUser');

        $this->assertNotEmpty($actualToken);
        $this->assertInternalType('string', $actualToken);
    }

    public function testAuthByUsernamePasswordTestUser()
    {
        $service = $this->getServiceInstance();

        $this->redisClientMock->expects($this->once())
            ->method('set')
            ->with(
                $this->matchesRegularExpression('/auth:token:[a-zA-Z0-9]{32}/'),
                $this->equalTo('testUser'),
                $this->equalTo(864000), // 10 days
                $this->equalTo(false),
                $this->equalTo('NX')
            )
            ->willReturn(new Success(true))
        ;

        /** @var User $actualUser */
        list($actualUser, $actualToken) = \Amp\Promise\wait(
            $service->authByUsernamePassword('testUser', 'testPassword')
        );

        $this->assertInstanceOf(User::class, $actualUser);
        $this->assertEquals($actualUser->getUsername(), 'testUser');

        $this->assertNotEmpty($actualToken);
        $this->assertInternalType('string', $actualToken);
    }

    public function testAuthByUsernamePasswordUserNotFound()
    {
        $service = $this->getServiceInstance();

        $this->redisClientMock->expects($this->never())
            ->method('set')
        ;

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid username or password');

        \Amp\Promise\wait($service->authByUsernamePassword('notFound', 'Wrong'));
    }

    public function testAuthByUsernamePasswordWrongPassword()
    {
        $service = $this->getServiceInstance();

        $user = new User(
            'testUser',
            '$2y$10$kEYXDhRhNmS1mk226hurv.i23tmnFXuqa1LCMG7UoyhZ3nF/PK7a2'
        );

        $this->modelManager->persist($user);
        $this->redisClientMock->expects($this->never())
            ->method('set')
        ;

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid username or password');

        \Amp\Promise\wait($service->authByUsernamePassword('testUser', 'Wrong'));
    }

    public function testAuthByUsernamePasswordTokenAlready()
    {
        $service = $this->getServiceInstance();

        $user = new User(
            'testUser',
            '$2y$10$kEYXDhRhNmS1mk226hurv.i23tmnFXuqa1LCMG7UoyhZ3nF/PK7a2'
        );

        $this->modelManager->persist($user);

        $this->redisClientMock->expects($this->exactly(2))
            ->method('set')
            ->with(
                $this->matchesRegularExpression('/auth:token:[a-zA-Z0-9]{32}/'),
                $this->equalTo('testUser'),
                $this->equalTo(864000), // 10 days
                $this->equalTo(false),
                $this->equalTo('NX')
            )
            ->willReturnOnConsecutiveCalls(
                new Success(false),
                new Success(true)
            )
        ;

        /** @var User $actualUser */
        list($actualUser, $actualToken) = \Amp\Promise\wait(
            $service->authByUsernamePassword("testUser", "testPassword")
        );

        $this->assertInstanceOf(User::class, $actualUser);
        $this->assertEquals($actualUser->getUsername(), 'testUser');

        $this->assertNotEmpty($actualToken);
        $this->assertInternalType('string', $actualToken);
    }

    public function testAuthByToken()
    {
        $service = $this->getServiceInstance();

        $this->redisClientMock->expects($this->once())
            ->method('get')
            ->with(
                $this->matchesRegularExpression('/auth:token:auth-token/')
            )
            ->willReturn(new Success("testUser"))
        ;

        $actualUsername = \Amp\Promise\wait(
            $service->authByToken("auth-token")
        );

        $this->assertEquals($actualUsername, 'testUser');
    }

    public function testAuthByTokenTokenNotExists()
    {
        $service = $this->getServiceInstance();

        $this->redisClientMock->expects($this->once())
            ->method('get')
            ->with(
                $this->matchesRegularExpression('/auth:token:auth-token/')
            )
            ->willReturn(new Success(null))
        ;

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid or expired token');

        \Amp\Promise\wait($service->authByToken("auth-token"));
    }
}
