<?php declare(strict_types = 1);
namespace Tests\Fedot\DataStorage\Redis;

use Amp\Redis\Client;
use Amp\Success;
use Fedot\DataStorage\Identifiable as IdentifiableInterface;
use Fedot\DataStorage\Redis\FetchManager;
use Fedot\DataStorage\Redis\KeyGenerator;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Fedot\Backlog\BaseTestCase;
use Tests\Fedot\DataStorage\Stubs\Identifiable;
use Tests\Fedot\DataStorage\Stubs\NotIdentifiable;
use TypeError;

class FetchManagerTest extends BaseTestCase
{
    /**
     * @var Client|PHPUnit_Framework_MockObject_MockObject
     */
    private $redisClientMock;

    /**
     * @var SerializerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    public function getInstance(): FetchManager
    {
        $this->redisClientMock = $this->createMock(Client::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        return new FetchManager(
            new KeyGenerator(),
            $this->redisClientMock,
            $this->serializerMock
        );
    }

    public function testFetchByIdFound()
    {
        $instance = $this->getInstance();

        $className = Identifiable::class;

        $this->redisClientMock
            ->expects($this->once())
            ->method('get')
            ->with('entity:tests_fedot_datastorage_stubs_identifiable:test-id')
            ->willReturn(new Success('saved-json'))
        ;

        $this->serializerMock
            ->expects($this->once())
            ->method('deserialize')
            ->with('saved-json', $className, 'json')
            ->willReturn(new Identifiable('test-id'))
        ;

        $actualResult = \Amp\wait($instance->fetchById($className, 'test-id'));

        $this->assertInstanceOf(Identifiable::class, $actualResult);
        $this->assertEquals('test-id', $actualResult->id);
    }

    public function testFetchByIdNotFound()
    {
        $instance = $this->getInstance();

        $className = Identifiable::class;

        $this->redisClientMock
            ->expects($this->once())
            ->method('get')
            ->with('entity:tests_fedot_datastorage_stubs_identifiable:test-id')
            ->willReturn(new Success(null))
        ;

        $this->serializerMock
            ->expects($this->never())
            ->method('deserialize')
        ;

        $actualResult = \Amp\wait($instance->fetchById($className, 'test-id'));

        $this->assertNull($actualResult);
    }

    public function testFetchByIdNotIdentifiable()
    {
        $instance = $this->getInstance();

        $className = NotIdentifiable::class;

        $this->redisClientMock
            ->expects($this->never())
            ->method('get')
        ;

        $this->serializerMock
            ->expects($this->never())
            ->method('deserialize')
        ;

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("{$className} not implemented " . IdentifiableInterface::class);

        \Amp\wait($instance->fetchById($className, 'test-id'));
    }
}
