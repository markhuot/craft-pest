<?php

namespace markhuot\craftpest\traits;

use React\ChildProcess\Process;
use React\EventLoop\Loop;

trait Serve
{
    protected $socket;
    protected $http;

    function serve($port=0)
    {
        if (!empty($this->socket)) {
            return $this;
        }

        $loop = Loop::get();

        $this->http = new \React\Http\HttpServer(function (\Psr\Http\Message\ServerRequestInterface $request) {
            $uri = $request->getUri();
            $response = $this->get($uri->getPath());
            return \React\Http\Message\Response::plaintext(
                $response->content
            );
        });
        
        $this->socket = new \React\Socket\SocketServer('127.0.0.1:'.$port);
        $this->http->listen($this->socket);

        $loop->addSignal(SIGINT, fn () => $loop->stop());

        return $this;
    }

    function getServerAddress()
    {
        return $this->socket->getAddress();
    }
}
