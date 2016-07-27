<?php
namespace Tests\Fedot\Backlog;

use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;
use PHPUnit_Framework_MockObject_MockObject;

class AuthenticationServiceTest extends BaseTestCase
{
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

        return new AuthenticationService($this->redisClientMock);
    }

    public function testAuthByUsernamePassword()
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
        list($actualUser, $actualToken) = \Amp\wait(
            $service->authByUsernamePassword("testUser", "testPassword")
        );

        $this->assertInstanceOf(User::class, $actualUser);
        $this->assertEquals($actualUser->username, 'testUser');

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

        \Amp\wait($service->authByUsernamePassword('notFound', 'Wrong'));
    }

    public function testAuthByUsernamePasswordWrongPassword()
    {
        $service = $this->getServiceInstance();

        $this->redisClientMock->expects($this->never())
            ->method('set')
        ;

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid username or password');

        \Amp\wait($service->authByUsernamePassword('testUser', 'Wrong'));
    }

    public function testAuthByUsernamePasswordTokenAlready()
    {
        $service = $this->getServiceInstance();

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
        list($actualUser, $actualToken) = \Amp\wait(
            $service->authByUsernamePassword("testUser", "testPassword")
        );

        $this->assertInstanceOf(User::class, $actualUser);
        $this->assertEquals($actualUser->username, 'testUser');

        $this->assertNotEmpty($actualToken);
        $this->assertInternalType('string', $actualToken);
    }
}
