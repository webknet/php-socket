<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\TodoServer;

require dirname(__DIR__) . '/html/vendor/autoload.php';

// Servidor WebSocket
$wsServer = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TodoServer()
        )
    ),
    8080
);

$wsServer->run();
