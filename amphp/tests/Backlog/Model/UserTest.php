<?php declare(strict_types = 1);

namespace Tests\Fedot\Backlog\Model;

use Fedot\Backlog\Model\User;
use Tests\Fedot\Backlog\BaseTestCase;

class UserTest extends BaseTestCase
{
    public function testGetId()
    {
        $user = new User();
        $user->username = 'username1';

        $this->assertEquals('username1', $user->getId());
    }
}
