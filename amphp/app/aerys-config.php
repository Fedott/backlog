<?php

$root = Aerys\root(__DIR__."/../../react-simple/web");

$serializer = new \Symfony\Component\Serializer\Serializer([
    new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer(),
], [
    new \Symfony\Component\Serializer\Encoder\JsonEncoder(),
]);
$serializerService = new \Fedot\Backlog\SerializerService($serializer);
$serializerService->addPayloadType('ping', \Fedot\Backlog\Response\Payload\EmptyPayload::class);

$requestProcessor = new \Fedot\Backlog\Request\RequestProcessorManager();
$requestProcessor->addProcessor(new \Fedot\Backlog\Request\Processor\Ping());

$websocket = \Aerys\websocket(new \Fedot\Backlog\WebSocketServer(
    $serializerService,
    $requestProcessor
));

$router = \Aerys\router()
    ->route('GET', '/websocket', $websocket)
;

(new Aerys\Host)
    ->expose('0.0.0.0', 8080)
    ->use($router)
    ->use($root)
;
