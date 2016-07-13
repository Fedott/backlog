<?php
use function DI\add;
use function DI\get;
use function DI\object;
use Fedot\Backlog\Request\Processor;
use Fedot\Backlog\Request\RequestProcessorManager;
use Fedot\Backlog\Response\Payload;
use Fedot\Backlog\SerializerService;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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

    'serializer-service.payloads' => add([
        'ping' => Payload\EmptyPayload::class,
    ]),
    SerializerService::class => object()
        ->method('addPayloadTypes', get('serializer-service.payloads')),

    'request.processors' => add([
        get(Processor\Ping::class),
    ]),
    RequestProcessorManager::class => object()
        ->method('addProcessors', get('request.processors')),

];
