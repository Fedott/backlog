<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Model;

use Fedot\Backlog\Model\User;
use Tests\Fedot\Backlog\BaseTestCase;

class UserTest extends BaseTestCase
{
    public function testGetId()
    {
        $user = new User('testUser', 'hash');

        $this->assertEquals('testUser', $user->getUsername());
        $this->assertEquals('hash', $user->getPasswordHash());
    }
}
