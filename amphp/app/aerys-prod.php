<?php
declare(strict_types = 1);
use Aerys\Host;
use Aerys\Request;
use Aerys\Response;

require_once __DIR__ . "/bootstrap.php";

$root = Aerys\root(__DIR__."/../web");

$container->compile();

$webSocketServer = $container->get('backlog.web-socket.server');
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
            $response->end(file_get_contents(__DIR__ . "/../web/index.html"));
        };
    }
};

$http = (new Host())
    ->expose('*', 443)
    ->name("backlog.fedot.name")
    ->encrypt(
        '/etc/letsencrypt/live/new-backlog.fedot.name/fullchain.pem',
        '/etc/letsencrypt/live/new-backlog.fedot.name/privkey.pem'
    )
    ->use($router)
    ->use($root)
    ->use($reWriter)
;

$http = (new Host())
    ->expose('*', 443)
    ->name("new-backlog.fedot.name")
    ->encrypt(
        '/etc/letsencrypt/live/new-backlog.fedot.name/fullchain.pem',
        '/etc/letsencrypt/live/new-backlog.fedot.name/privkey.pem'
    )
    ->redirect('https://backlog.fedot.name')
;

(new Host)
    ->expose("*", 80)
    ->name("new-backlog.fedot.name")
    ->redirect('https://backlog.fedot.name')
;
(new Host)
    ->expose("*", 80)
    ->name("backlog.fedot.name")
    ->redirect('https://backlog.fedot.name')
;
