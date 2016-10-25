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
;

$reWriter = new class implements \Aerys\Bootable
{
    /**
     * @inheritdoc
     */
    function boot(\Aerys\Server $server, \Psr\Log\LoggerInterface $logger)
    {
        return function (Request $request, Response $response) {
            $response->end(file_get_contents(__DIR__ . "/../web/index.html"));
        };
    }
};

(new Aerys\Host)
    ->expose('*', 80)
    ->name("new-backlog.fedot.name")
//    ->encrypt(__DIR__ . '/keys/crt', __DIR__ . '/keys/key')
    ->use($router)
    ->use($root)
    ->use($reWriter)
;
