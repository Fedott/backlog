<?php declare(strict_types = 1);
namespace Tests\Fedot\DataStorage\Redis;

use Amp\Redis\Client;
use Amp\Success;
use Fedot\DataStorage\Redis\KeyGenerator;
use Fedot\DataStorage\Redis\RelationshipManager;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\Fedot\Backlog\BaseTestCase;
use Tests\Fedot\DataStorage\Stubs\Identifiable;
use Fedot\DataStorage\Identifiable as IdentifiableInterface;
use Tests\Fedot\DataStorage\Stubs\NotIdentifiable;
use TypeError;

class RelationshipManagerTest extends BaseTestCase
{
    /**
     * @var Client|PHPUnit_Framework_MockObject_MockObject
     */
    private $redisClientMock;

    private function getInstance(): RelationshipManager
    {
        $this->redisClientMock = $this->createMock(Client::class);

        return new RelationshipManager(new KeyGenerator(), $this->redisClientMock);
    }

    public function testAddOneToMany()
    {
        $instance = $this->getInstance();

        $forModel = new Identifiable('for-id');
        $model = new Identifiable('test-id');

        $this->redisClientMock->expects($this->once())
            ->method('lPush')
            ->with('index:tests_fedot_datastorage_stubs_identifiable:for-id:tests_fedot_datastorage_stubs_identifiable', 'test-id')
            ->willReturn(new Success(1))
        ;

        $result = \Amp\wait($instance->addOneToMany($forModel, $model));

        $this->assertSame(1, $result);
    }

    public function testGetIdsOneToMany()
    {
        $instance = $this->getInstance();

        $forModel = new Identifiable('for-id');
        $modelClassName = Identifiable::class;

        $this->redisClientMock->expects($this->once())
            ->method('lRange')
            ->with('index:tests_fedot_datastorage_stubs_identifiable:for-id:tests_fedot_datastorage_stubs_identifiable', 0, -1)
            ->willReturn(new Success([
                'test-id1',
                'test-id2',
                'test-id4',
            ]))
        ;

        $result = \Amp\wait($instance->getIdsOneToMany($forModel, $modelClassName));

        $this->assertSame([
            'test-id1',
            'test-id2',
            'test-id4',
        ], $result);
    }

    public function testGetIdsOneToManyEmpty()
    {
        $instance = $this->getInstance();

        $forModel = new Identifiable('for-id');
        $modelClassName = Identifiable::class;

        $this->redisClientMock->expects($this->once())
            ->method('lRange')
            ->with('index:tests_fedot_datastorage_stubs_identifiable:for-id:tests_fedot_datastorage_stubs_identifiable', 0, -1)
            ->willReturn(new Success([]))
        ;

        $result = \Amp\wait($instance->getIdsOneToMany($forModel, $modelClassName));

        $this->assertSame([], $result);
    }

    public function testGetIdsOneToManyNotIdentifiable()
    {
        $instance = $this->getInstance();

        $forModel = new Identifiable('for-id');
        $className = NotIdentifiable::class;

        $this->redisClientMock->expects($this->never())
            ->method($this->anything())
        ;

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("{$className} not implemented " . IdentifiableInterface::class);

        \Amp\wait($instance->getIdsOneToMany($forModel, $className));
    }

    public function testRemoveOneToMany()
    {
        $instance = $this->getInstance();

        $forModel = new Identifiable('for-id');
        $model = new Identifiable('test-id');

        $this->redisClientMock->expects($this->once())
            ->method('lRem')
            ->with('index:tests_fedot_datastorage_stubs_identifiable:for-id:tests_fedot_datastorage_stubs_identifiable', 'test-id')
            ->willReturn(new Success(1))
        ;

        $result = \Amp\wait($instance->removeOneToMany($forModel, $model));

        $this->assertSame(1, $result);
    }
}
