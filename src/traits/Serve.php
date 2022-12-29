<?php

namespace markhuot\craftpest\traits;

use React\ChildProcess\Process;
use React\EventLoop\Loop;

trait Serve
{
    function serve()
    {
        $loop = Loop::get();

        $http = new \React\Http\HttpServer(function (\Psr\Http\Message\ServerRequestInterface $request) {
            $uri = $request->getUri();
            $response = $this->get($uri->getPath());
            return \React\Http\Message\Response::plaintext(
                $response->content
            );
        });
        
        $socket = new \React\Socket\SocketServer('127.0.0.1:9001');
        $http->listen($socket);

        $loop->addSignal(SIGINT, fn () => $loop->stop());
        
        //$timeout = 5;
        //$loop->addTimer($timeout, fn () => $loop->stop());

        $process = new Process('exec curl http://127.0.0.1:9001');
        $process->start();

        $stdout = '';
        $process->stdout->on('data', function($chunk) use (&$stdout) {
            $stdout .= $chunk;
        });
        
        $exitCode = null;
        $process->on('exit', function ($c) use (&$exitCode, $loop) {
            $exitCode = $c;
            $loop->stop();
        });

        $loop->run();

        return (object) [
            'exitCode' => $exitCode,
            'stdout' => $stdout,
        ];
        
        // $this->assertSame(0, $exitCode);
        // $this->assertSame("Hello World!\n", $stdout);
    }
}