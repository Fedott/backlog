<?php
declare(strict_types = 1);
use Aerys\Request;
use Aerys\Response;
use Fedot\Backlog\WebSocketServer;

require_once __DIR__ . "/bootstrap.php";

$root = Aerys\root(__DIR__."/../web");

$container->compile(true);

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
    public function boot(\Aerys\Server $server, \Psr\Log\LoggerInterface $logger)
    {
        return function (Request $request, Response $response) {
            $response->end(file_get_contents(realpath(__DIR__ . "/../web/index.html")));
        };
    }
};

(new Aerys\Host)
    ->expose('*', 8080)
    ->use($router)
    ->use($root)
    ->use($reWriter)
;
