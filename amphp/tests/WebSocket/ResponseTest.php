<?php

namespace Tests\Fedot\Backlog\WebSocket;


use Fedot\Backlog\WebSocket\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestId()
    {
        $response1 = new Response(7, 5, 'test');

        $response2 = $response1->withRequestId(17);

        $this->assertNotSame($response1, $response2);
        $this->assertSame(7, $response1->getRequestId());
        $this->assertSame(17, $response2->getRequestId());
    }

    public function testClientId()
    {
        $response1 = new Response(7, 5, 'test');

        $response2 = $response1->withClientId(15);

        $this->assertNotSame($response1, $response2);
        $this->assertSame(5, $response1->getClientId());
        $this->assertSame(15, $response2->getClientId());
    }

    public function testType()
    {
        $response1 = new Response(7, 5, 'test');

        $response2 = $response1->withType('type1');
        $response3 = $response2->withType('other-type');

        $this->assertNotSame($response1, $response2);
        $this->assertNotSame($response2, $response3);
        $this->assertNotSame($response1, $response3);
        $this->assertSame('test', $response1->getType());
        $this->assertSame('type1', $response2->getType());
        $this->assertSame('other-type', $response3->getType());
    }

    public function testPayload()
    {
        $response1 = new Response(7, 5, 'test');

        $payload2 = ['test' => 2];
        $payload3 = ['prop' => ['qwe' => 'fgg']];

        $response2 = $response1->withPayload($payload2);
        $response3 = $response2->withPayload($payload3);

        $this->assertNotSame($response1, $response2);
        $this->assertNotSame($response2, $response3);
        $this->assertNotSame($response1, $response3);
        $this->assertSame([], $response1->getPayload());
        $this->assertSame($payload2, $response2->getPayload());
        $this->assertSame($payload3, $response3->getPayload());
    }

    public function testIsDirect()
    {
        $response1 = new Response(7, 5, 'test');

        $response2 = $response1->withIsDirect(false);

        $this->assertNotSame($response1, $response2);
        $this->assertSame(true, $response1->isDirect());
        $this->assertSame(false, $response2->isDirect());
    }
}
