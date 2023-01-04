<?php

namespace markhuot\craftpest\traits;

use markhuot\craftpest\process\TestableResult;
use Pest\Support\Container;
use React\ChildProcess\Process;
use React\EventLoop\Loop;
use Symfony\Component\Console\Output\OutputInterface;

trait Playwright
{
    protected $processes = [];

    function exec(array $cmd, $cwd=null, $env=[])
    {
        $loop = Loop::get();

        $process = new Process(implode(' ', $cmd), $cwd, array_merge(getenv(), $env));
        $process->start($loop);

        $output = '';
        $stdout = '';
        $process->stdout->on('data', function($chunk) use (&$stdout, &$output) {
            $stdout .= $chunk;
            $output .= $chunk;
        });
        $stderr = '';
        $process->stderr->on('data', function ($chunk) use (&$stderr, &$output) {
            $stderr .= $chunk;
            $output .= $chunk;
        });

        $exitCode = null;
        $process->on('exit', function ($c) use (&$exitCode, $loop) {
            $exitCode = $c;
            $loop->stop();
        });

        $loop->run();

        $result = new TestableResult();
        $result->setExitCode($exitCode);
        $result->setStdout($stdout);
        $result->setStderr($stderr);
        $result->setOutput($output);

        $this->processes[] = ['process' => $process, 'result' => $result];
        Container::getInstance()->add('processes', $this->processes);

        return $result;
    }

    function playwright()
    {
        $address = $this->serve()->getServerAddress();
        $port = parse_url($address, PHP_URL_PORT);

        return $this->exec(['npx', 'playwright', 'test', '--reporter=dot'], null, [
            'PLAYWRIGHT_BASE_URL' => 'http://127.0.0.1:' . $port,
        ]);
    }
}
