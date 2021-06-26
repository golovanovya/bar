<?php

require __DIR__ . "/vendor/autoload.php";

use Amp\ByteStream\ResourceOutputStream;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Loop;
use Amp\Socket\Server;
use enaza\GenreFactory;
use enaza\Pub;
use enaza\Visitor;
use Monolog\Logger;

$pub = new Pub(new GenreFactory());

Loop::run(static function () use ($pub) {
    $servers = [
        Server::listen("0.0.0.0:8080"),
    ];

    $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
    $logHandler->setFormatter(new ConsoleFormatter());
    $logger = new Logger('server');
    $logger->pushHandler($logHandler);

    $router = new Router();

    $router->addRoute('GET', '/', new CallableRequestHandler(function () use ($pub) {
        return new Response(
            Status::OK,
            [
                "Content-type" => "application/json; charset=utf-8",
                "Access-Control-Allow-Origin" => "*",
            ],
            json_encode([
                "playing" => $pub->getCurrentGenre()->getName(),
                "dance_floor" => array_map(fn (Visitor $visitor) => ["name" => $visitor->getName(), "favorite_genre" => $visitor->getGenre()->getName()], $pub->getDanceFloorVisitors()),
                "bar" => array_map(fn (Visitor $visitor) => ["name" => $visitor->getName(), "favorite_genre" => $visitor->getGenre()->getName()], $pub->getBarVisitors()),
            ])
        );
    }));

    $router->addRoute('POST', '/genre', new CallableRequestHandler(function () use ($pub) {
        $pub->changeGenre();
        return new Response(
            Status::OK,
            [
                "Content-type" => "application/json; charset=utf-8",
                "Access-Control-Allow-Origin" => "*",
            ],
            json_encode(["result" => "ok", "genre" => $pub->getCurrentGenre()->getName()])
        );
    }));

    $server = new HttpServer($servers, $router, $logger);

    yield $server->start();

    Loop::onSignal(SIGINT, static function (string $watcherId) use ($server) {
        Loop::cancel($watcherId);
        yield $server->stop();
    });
});
