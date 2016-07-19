<?php
namespace Tests\Fedot\Backlog;

use Amp\Promise;
use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\StoriesRepository;
use Symfony\Component\Serializer\SerializerInterface;

class StoriesRepositoryTest extends BaseTestCase
{
    public function testGetAll()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $keys = ["story:543", "story:123", "story:765"];
        $redisClientMock->expects($this->once())
            ->method('keys')
            ->with($this->equalTo("story:*"))
            ->willReturn(new Success($keys))
        ;
        $redisClientMock->expects($this->once())
            ->method('mGet')
            ->with($this->equalTo($keys))
            ->willReturn(new Success([
                "first",
                "second",
                "story 3",
            ]))
        ;
        $serializerMock->expects($this->exactly(3))
            ->method('deserialize')
            ->withConsecutive(
                ["first", Story::class, "json"],
                ["second", Story::class, "json"],
                ["story 3", Story::class, "json"]
            )
            ->willReturnOnConsecutiveCalls(
                new Story(),
                new Story(),
                new Story()
            )
        ;

        $resultPromise = $repository->getAll();
        $this->assertInstanceOf(Promise::class, $resultPromise);

        $result = \Amp\wait($resultPromise);

        $this->assertCount(3, $result);
        array_map(function($story) {
            $this->assertInstanceOf(Story::class, $story);
        }, $result);
    }

    public function testCreate()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $story = new Story();
        $story->id = 'gjfhjdjfh';

        $redisPromise = new Success(true);
        $redisClientMock->expects($this->once())
            ->method('setNx')
            ->with("story:gjfhjdjfh", "{json-mock}")
            ->willReturn($redisPromise)
        ;

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($story, 'json')
            ->willReturn("{json-mock}")
        ;

        $resultPromise = $repository->create($story);
        $this->assertEquals($redisPromise, $resultPromise);
    }

    public function testDelete()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);
        $redisDelPromise = new Success(true);
        $redisClientMock->expects($this->once())
            ->method('del')
            ->with('story:storyId333')
            ->willReturn($redisDelPromise)
        ;

        $resultPromise = $repository->delete('storyId333');
        $this->assertEquals($redisDelPromise, $resultPromise);
    }

    public function testSave()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $story = new Story();
        $story->id = 'gjfhjdjfh';

        $redisPromise = new Success(true);
        $redisClientMock->expects($this->once())
            ->method('set')
            ->with("story:gjfhjdjfh", "{json-mock}")
            ->willReturn($redisPromise)
        ;

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($story, 'json')
            ->willReturn("{json-mock}")
        ;

        $resultPromise = $repository->save($story);
        $this->assertEquals($redisPromise, $resultPromise);
    }
}
