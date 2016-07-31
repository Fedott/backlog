<?php

namespace Tests\Fedot\Backlog;


use Fedot\Backlog\Model\User;
use Fedot\Backlog\WebSocketConnectionAuthenticationService;

class WebSocketConnectionAuthenticationServiceTest extends BaseTestCase
{
    public function testFullFlow()
    {
        $service = new WebSocketConnectionAuthenticationService();

        $user1 = new User();
        $user2 = new User();
        $user3 = new User();

        $service->authorizeClient(34, $user1);
        $service->authorizeClient(23, $user2);
        $service->authorizeClient(35, $user3);

        $this->assertFalse($service->isAuthorizedClient(10));
        $this->assertTrue($service->isAuthorizedClient(34));
        $this->assertTrue($service->isAuthorizedClient(23));
        $this->assertTrue($service->isAuthorizedClient(35));

        $this->assertEquals($user2, $service->getAuthorizedUserForClient(23));
        $this->assertEquals($user3, $service->getAuthorizedUserForClient(35));
        $this->assertEquals($user1, $service->getAuthorizedUserForClient(34));

        $service->unauthorizeClient(23);
        $this->assertTrue($service->isAuthorizedClient(34));
        $this->assertTrue($service->isAuthorizedClient(35));
        $this->assertFalse($service->isAuthorizedClient(23));

        $service->unauthorizeClient(35);
        $this->assertTrue($service->isAuthorizedClient(34));
        $this->assertFalse($service->isAuthorizedClient(35));
        $this->assertFalse($service->isAuthorizedClient(23));

        $service->unauthorizeClient(34);
        $this->assertFalse($service->isAuthorizedClient(34));
        $this->assertFalse($service->isAuthorizedClient(35));
        $this->assertFalse($service->isAuthorizedClient(23));
    }

    public function testNotFoundAuthorizedUser()
    {
        $service = new WebSocketConnectionAuthenticationService();

        $user1 = new User();
        $user2 = new User();
        $user3 = new User();

        $service->authorizeClient(34, $user1);
        $service->authorizeClient(23, $user2);
        $service->authorizeClient(35, $user3);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Client 55 not authorized");

        $service->getAuthorizedUserForClient(55);
    }
}
