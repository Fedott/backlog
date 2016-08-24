<?php
use Fedot\Backlog\WebSocketServer;

require_once __DIR__ . "/bootstrap.php";

$root = Aerys\root(__DIR__."/../web");

$webSocketServer = $container->get(WebSocketServer::class);
$websocket       = \Aerys\websocket($webSocketServer);

$router = \Aerys\router()
    ->route('GET', '/websocket', $websocket)
;

(new Aerys\Host)
    ->expose('*', 443)
    ->name("new-backlog.fedot.name")
    ->encrypt(__DIR__ . '/keys/crt', __DIR__ . '/keys/key')
    ->use($router)
    ->use($root)
;
