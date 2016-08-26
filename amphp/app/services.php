<?php
use function DI\add;
use function DI\get;
use function DI\object;
use Fedot\Backlog\Model\Story;
use Fedot\Backlog\Request\Processor;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\Payload;
use Fedot\Backlog\SerializerService;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

return [
    'serializer.normalazers' => add([
        get(ObjectNormalizer::class)
    ]),
    'serializer.encoders' => add([
        get(JsonEncoder::class)
    ]),
    Serializer::class => object()
        ->constructor(
            get('serializer.normalazers'),
            get('serializer.encoders')
        ),
    SerializerInterface::class => get(Serializer::class),

    'request.processors' => add([
        get(Processor\Ping::class),
        get(Processor\GetStories::class),
        get(Processor\CreateStory::class),
        get(Processor\DeleteStory::class),
        get(Processor\EditStory::class),
        get(Processor\MoveStory::class),
        get(Processor\LoginUsernamePassword::class),
        get(Processor\LoginToken::class),
    ]),
    RequestProcessorManager::class => object()
        ->method('addProcessors', get('request.processors')),
    SerializerService::class => object()
        ->method('addAllPayloadTypesFromProcessors', get('request.processors')),

    Amp\Redis\Client::class => object()
        ->constructor(get('redis.uri')),
];
