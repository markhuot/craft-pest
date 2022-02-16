<?php

namespace markhuot\craftpest\services;

use craft\web\Application;
use craft\web\Response;
use GuzzleHttp\Psr7\Message;
use markhuot\craftpest\web\Request;
use Symfony\Component\Process\Process;
use yii\base\Event;

class Http
{
    /** @var Response */
    public $response;

    /**
     * Example description.
     */
    public function get(string $uri=null): \markhuot\craftpest\test\Response
    {
        $binPath = realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'bin', 'serve']));
        $process = new Process([$binPath, '-v', CRAFT_VENDOR_PATH, $uri]);
        $process->setTimeout(null);
        $process->run();

        $output = $process->getOutput();

        $data = Message::parseMessage($output);
        $parts = explode(' ', $data['start-line'], 3);
        return new \markhuot\craftpest\test\Response(
            (int) $parts[1],
            $data['headers'],
            $data['body'],
            explode('/', $parts[0])[1],
            $parts[2] ?? null
        );
    }
}
