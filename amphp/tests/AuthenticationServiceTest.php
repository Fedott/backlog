<?php
namespace Tests\Fedot\Backlog;

use Fedot\Backlog\AuthenticationService;
use Fedot\Backlog\Exception\AuthenticationException;
use Fedot\Backlog\Model\User;

class AuthenticationServiceTest extends BaseTestCase
{
    public function testAuthByUsernamePassword()
    {
        $service = new AuthenticationService();

        /** @var User $actualUser */
        list($actualUser, $actualToken) = $service->authByUsernamePassword("testUser", "testPassword");

        $this->assertInstanceOf(User::class, $actualUser);
        $this->assertEquals($actualUser->username, 'testUser');

        $this->assertNotEmpty($actualToken);
        $this->assertInternalType('string', $actualToken);
    }

    public function testAuthByUsernamePasswordUserNotFound()
    {
        $service = new AuthenticationService();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid username or password');

        $service->authByUsernamePassword('notFound', 'Wrong');
    }

    public function testAuthByUsernamePasswordWrongPassword()
    {
        $service = new AuthenticationService();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid username or password');

        $service->authByUsernamePassword('testUser', 'Wrong');
    }
}
