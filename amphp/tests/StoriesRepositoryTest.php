<?php
namespace Tests\Fedot\Backlog;

use Amp\Promise;
use Amp\Redis\Client;
use Amp\Success;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Model\User;
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
            ->method('lRange')
            ->with($this->equalTo("stories:sort:default"), $this->equalTo(0), $this->equalTo(-1))
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

    public function testGetAllEmpty()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $keys = [];
        $redisClientMock->expects($this->once())
            ->method('lRange')
            ->with($this->equalTo("stories:sort:default"), $this->equalTo(0), $this->equalTo(-1))
            ->willReturn(new Success($keys))
        ;
        $redisClientMock->expects($this->never())
            ->method('mGet')
        ;
        $serializerMock->expects($this->never())
            ->method('deserialize')
        ;

        $resultPromise = $repository->getAll();
        $this->assertInstanceOf(Promise::class, $resultPromise);

        $result = \Amp\wait($resultPromise);

        $this->assertCount(0, $result);
        $this->assertEquals([], $result);
    }

    public function testCreate()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $story = new Story();
        $story->id = 'gjfhjdjfh';

        $redisSetNXPromise = new Success(true);
        $redisLPushPromise = new Success(true);
        $redisClientMock->expects($this->once())
            ->method('setNx')
            ->with("story:gjfhjdjfh", "{json-mock}")
            ->willReturn($redisSetNXPromise)
        ;

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($story, 'json')
            ->willReturn("{json-mock}")
        ;

        $redisClientMock->expects($this->once())
            ->method('lPush')
            ->with("stories:sort:default", "story:gjfhjdjfh")
            ->willReturn($redisLPushPromise)
        ;

        $resultPromise = $repository->create($story);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testCreateErrorAlreadyExist()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $story = new Story();
        $story->id = 'gjfhjdjfh';

        $redisSetNXPromise = new Success(false);
        $redisClientMock->expects($this->once())
            ->method('setNx')
            ->with("story:gjfhjdjfh", "{json-mock}")
            ->willReturn($redisSetNXPromise)
        ;

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($story, 'json')
            ->willReturn("{json-mock}")
        ;

        $redisClientMock->expects($this->never())
            ->method('lPush')
        ;

        $resultPromise = $repository->create($story);
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(false, $result);
    }

    public function testDelete()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);
        $redisDelPromise = new Success(1);
        $redisLRemPromise = new Success(true);
        $redisClientMock->expects($this->once())
            ->method('del')
            ->with('story:storyId333')
            ->willReturn($redisDelPromise)
        ;

        $redisClientMock->expects($this->once())
            ->method('lRem')
            ->with("stories:sort:default", "story:storyId333", 1)
            ->willReturn($redisLRemPromise)
        ;

        $resultPromise = $repository->delete('storyId333');
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
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

        $user = new User();
        $user->username = 'testUser';

        $resultPromise = $repository->save($user, $story);
        $this->assertEquals($redisPromise, $resultPromise);
    }

    public function testMovePositive()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $redisClientMock->expects($this->once())
            ->method('lRem')
            ->with("stories:sort:default", 'story:storyId333', 1)
            ->willReturn(new Success(1))
        ;

        $redisClientMock->expects($this->once())
            ->method('lInsert')
            ->with("stories:sort:default", 'before', "story:storyId888", "story:storyId333")
            ->willReturn(new Success(3))
        ;

        $resultPromise = $repository->move('storyId333', 'storyId888');
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testMoveNegativeRemove()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $redisClientMock->expects($this->once())
            ->method('lRem')
            ->with("stories:sort:default", 'story:storyId333', 1)
            ->willReturn(new Success(0))
        ;

        $redisClientMock->expects($this->once())
            ->method('lInsert')
            ->with("stories:sort:default", 'before', "story:storyId888", "story:storyId333")
            ->willReturn(new Success(3))
        ;

        $resultPromise = $repository->move('storyId333', 'storyId888');
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(true, $result);
    }

    public function testMoveNegativeInsert()
    {
        $redisClientMock = $this->createMock(Client::class);
        $serializerMock = $this->createMock(SerializerInterface::class);

        $repository = new StoriesRepository($redisClientMock, $serializerMock);

        $redisClientMock->expects($this->once())
            ->method('lRem')
            ->with("stories:sort:default", 'story:storyId333', 1)
            ->willReturn(new Success(1))
        ;

        $redisClientMock->expects($this->once())
            ->method('lInsert')
            ->with("stories:sort:default", 'before', "story:storyId888", "story:storyId333")
            ->willReturn(new Success(-1))
        ;

        $redisClientMock->expects($this->once())
            ->method('lPush')
            ->with('stories:sort:default', 'story:storyId333')
            ->willReturn(new Success(1))
        ;

        $resultPromise = $repository->move('storyId333', 'storyId888');
        $result = \Amp\wait($resultPromise);
        $this->assertEquals(false, $result);
    }
}
