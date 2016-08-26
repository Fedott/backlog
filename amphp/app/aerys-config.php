<?php
use Aerys\Request;
use Aerys\Response;
use Fedot\Backlog\WebSocketServer;

require_once __DIR__ . "/bootstrap.php";

$root = Aerys\root(__DIR__."/../web");

$webSocketServer = $container->get(WebSocketServer::class);
$websocket       = \Aerys\websocket($webSocketServer);

$router = \Aerys\router()
    ->route('GET', '/websocket', $websocket)
    ->route('GET', '/assets/bundle.js', function(Request $request, Response $response) {
        $response->addHeader('content-size', '1042755');
        $file = file_get_contents(__DIR__."/../web/assets/bundle.js");
        $response->end($file);
    })
    ->route('GET', '/assets/bundle.js.map', function(Request $request, Response $response) {
        $response->end(file_get_contents(__DIR__."/../web/assets/bundle.js.map"));
    })
;

(new Aerys\Host)
    ->name('backlog.local')
    ->expose('0.0.0.0', 8080)
//    ->encrypt(__DIR__ . '/keys/crt', __DIR__ . '/keys/key')
    ->use($router)
    ->use($root)
;
