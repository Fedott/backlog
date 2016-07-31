<?php
use Fedot\Backlog\WebSocketServer;

require_once __DIR__ . "/bootstrap.php";

$root = Aerys\root(__DIR__."/../../react-simple/web");

$webSocketServer = $container->get(WebSocketServer::class);
$websocket       = \Aerys\websocket($webSocketServer);

$router = \Aerys\router()
    ->route('GET', '/websocket', $websocket)
;

(new Aerys\Host)
    ->name('')
    ->expose('0.0.0.0', 8080)
    ->use($router)
    ->use($root)
;
