<?php

$root = Aerys\root(__DIR__."/../../react-simple/web");

$websocket = \Aerys\websocket(new \Fedot\Backlog\WebSocketServer());

$router = \Aerys\router()
    ->route('GET', '/websocket', $websocket)
;

(new Aerys\Host)
    ->expose('0.0.0.0', 8080)
    ->use($router)
    ->use($root)
;
