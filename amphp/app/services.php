<?php
declare(strict_types = 1);
use function DI\add;
use function DI\get;
use function DI\object;
use Fedot\Backlog\Infrastructure\Middleware\RunnerFactory;
use Fedot\Backlog\Middleware\PayloadParser;
use Fedot\Backlog\Middleware\RequestProcessor;
use Fedot\Backlog\Request\Processor;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\SerializerService;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

return [
    'serializer.typeExtractors' => [
        get(PhpDocExtractor::class),
    ],
    PropertyInfoExtractor::class => \DI\object()
        ->constructorParameter('typeExtractors', get('serializer.typeExtractors')),
    ObjectNormalizer::class => \DI\object()
        ->constructorParameter('propertyTypeExtractor', get(PropertyInfoExtractor::class)),
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

    RunnerFactory::class => object()
        ->constructor(get('middleware.queue')),

    'middleware.queue' => [
        get(PayloadParser::class),
        get(RequestProcessor::class),
    ],

    'request.processors' => add([
        get(Processor\Ping::class),
        get(Processor\GetStories::class),
        get(Processor\CreateStory::class),
        get(Processor\DeleteStory::class),
        get(Processor\EditStory::class),
        get(Processor\MoveStory::class),
        get(Processor\LoginUsernamePassword::class),
        get(Processor\LoginToken::class),
        get(Processor\ProjectCreate::class),
        get(Processor\GetProjects::class),
        get(Processor\MarkStoryAsCompleted::class),
        get(Processor\User\Registration::class),
    ]),
    RequestProcessorManager::class => object()
        ->method('addProcessors', get('request.processors')),
    SerializerService::class => object()
        ->method('addAllPayloadTypesFromProcessors', get('request.processors')),

    Amp\Redis\Client::class => object()
        ->constructor(get('redis.uri')),
];
