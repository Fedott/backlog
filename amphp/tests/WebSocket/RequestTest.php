<?php

namespace Tests\Fedot\Backlog\WebSocket;

use Fedot\Backlog\WebSocket\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testId()
    {
        $request1 = new Request(7, 'test', 5);

        $request2 = $request1->withId(17);

        $this->assertNotSame($request1, $request2);
        $this->assertSame(7, $request1->getId());
        $this->assertSame(17, $request2->getId());
    }

    public function testClientId()
    {
        $request1 = new Request(7, 'test', 5);

        $request2 = $request1->withClientId(15);

        $this->assertNotSame($request1, $request2);
        $this->assertSame(5, $request1->getClientId());
        $this->assertSame(15, $request2->getClientId());
    }

    public function testType()
    {
        $request1 = new Request(7, 'test', 5);

        $request2 = $request1->withType('type1');
        $request3 = $request2->withType('other-type');

        $this->assertNotSame($request1, $request2);
        $this->assertNotSame($request2, $request3);
        $this->assertNotSame($request1, $request3);
        $this->assertSame('test', $request1->getType());
        $this->assertSame('type1', $request2->getType());
        $this->assertSame('other-type', $request3->getType());
    }

    public function testAttributes()
    {
        $request1 = new Request(7, ' test', 5);

        $request2 = $request1->withAttribute('name', 'value');
        $request3 = $request2->withAttribute('other', 'otherValue');
        $request4 = $request3->withoutAttribute('other');
        $request5 = $request3->withoutAttribute('unknown');

        $this->assertNotSame($request2, $request1);
        $this->assertNotSame($request3, $request2);
        $this->assertNotSame($request4, $request3);
        $this->assertNotSame($request5, $request4);

        $this->assertEmpty($request1->getAttributes());
        $this->assertEmpty($request1->getAttribute('name'));

        $this->assertEquals(
            'something',
            $request1->getAttribute('name', 'something'),
            'Should return the default value'
        );

        $this->assertEquals('value', $request2->getAttribute('name'));
        $this->assertEquals(['name' => 'value'], $request2->getAttributes());
        $this->assertEquals(['name' => 'value', 'other' => 'otherValue'], $request3->getAttributes());
        $this->assertEquals(['name' => 'value'], $request4->getAttributes());
    }

    public function testNullAttribute()
    {
        $request = (new Request(7, 'test', 5))->withAttribute('name', null);

        $this->assertSame(['name' => null], $request->getAttributes());
        $this->assertNull($request->getAttribute('name', 'different-default'));

        $requestWithoutAttribute = $request->withoutAttribute('name');

        $this->assertSame([], $requestWithoutAttribute->getAttributes());
        $this->assertSame('different-default', $requestWithoutAttribute->getAttribute('name', 'different-default'));
    }
}
