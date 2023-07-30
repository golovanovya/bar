<?php

require __DIR__ . "/vendor/autoload.php";

use Amp\ByteStream\ResourceOutputStream;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Loop;
use Amp\Promise;
use Amp\Socket\Server;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Message;
use Amp\Websocket\Server\ClientHandler;
use Amp\Websocket\Server\Gateway;
use Amp\Websocket\Server\Websocket;
use Amp\Websocket\Server\WebsocketServerObserver;
use enaza\GenreChanged;
use enaza\GenreFactory;
use enaza\Pub;
use enaza\Visitor;
use League\Event\EventDispatcher;
use Monolog\Logger;

use function Amp\call;

$dispatcher = new EventDispatcher();
$pub = new Pub(new GenreFactory(), $dispatcher);

$websocket = new Websocket(new class ($dispatcher) implements ClientHandler, WebsocketServerObserver {
    private EventDispatcher $dispatcher;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onStart(HttpServer $server, Gateway $gateway): Promise
    {
        $this->dispatcher->subscribeTo(GenreChanged::class, function () use ($gateway) {
            $gateway->broadcast('genreChanged');
        });
        return new Success();
    }

    public function onStop(HttpServer $server, Gateway $gateway): Promise
    {
        return new Success();
    }
    
    public function handleHandshake(Gateway $gateway, Request $request, Response $response): Promise
    {
        return new Success($response);
    }

    public function handleClient(Gateway $gateway, Client $client, Request $request, Response $response): Promise
    {
        return call(function () use ($gateway, $client): \Generator {
            while ($message = yield $client->receive()) {
                \assert($message instanceof Message);
                $gateway->broadcast(\sprintf(
                    '%d: %s',
                    $client->getId(),
                    yield $message->buffer()
                ));
            }
        });
    }
});

Loop::run(static function () use ($pub, $websocket) {
    Loop::repeat(10000, fn() => $pub->changeGenre());
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
        return new Response(Status::NO_CONTENT, [
            "Content-type" => "application/json; charset=utf-8",
            "Access-Control-Allow-Origin" => "*",
        ]);
    }));

    $router->addRoute('GET', '/broadcast', $websocket);

    $server = new HttpServer($servers, $router, $logger);

    yield $server->start();

    Loop::onSignal(SIGINT, static function (string $watcherId) use ($server) {
        Loop::cancel($watcherId);
        yield $server->stop();
    });
});
