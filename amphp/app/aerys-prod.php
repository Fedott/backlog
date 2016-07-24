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
    ->expose('0.0.0.0', 80)
    ->name("new-backlog.fedot.name")
    ->use($router)
    ->use($root)
;
