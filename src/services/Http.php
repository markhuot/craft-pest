<?php

namespace markhuot\craftpest\services;

use craft\web\Application;
use craft\web\Response;
use craft\web\UrlManager;
use GuzzleHttp\Psr7\Message;
use markhuot\craftpest\web\Request;
use Symfony\Component\Process\Process;
use yii\base\Event;

class Http
{
    /**
     * Example description.
     */
    public function get(string $uri=null): \markhuot\craftpest\test\Response
    {
        $uri = ltrim($uri, '/');

        $request = (new \markhuot\craftpest\web\Request)->setRaw([
            '_isConsoleRequest' => false,
            '_fullPath' => $uri,
            '_path' => $uri,
            '_fullUri' => $uri,
            '_ipAddress' => '::1',
            '_rawBody' => '',
            '_bodyParams' => [],
            '_queryParams' => [],
            '_hostInfo' => 'http://localhost:8080',
            '_hostName' => 'localhost',
            '_baseUrl' => '',
            '_scriptUrl' => '/index.php',
            '_scriptFile' => '',
            '_pathInfo' => $uri,
            '_url' => "/{$uri}",
            '_port' => 8080,
        ]);

        $craft = \Craft::$app;

        // Run the application
        try {
            $craft->setComponents([
                'request' => $request,
                'urlManager' => [
                    'class' => \craft\web\UrlManager::class,
                    'enablePrettyUrl' => true,
                    'ruleConfig' => ['class' => \craft\web\UrlRule::class],
                ],
            ]);

            $craft->trigger(Application::EVENT_BEFORE_REQUEST);
            $response = $craft->handleRequest($request, true);
            $craft->trigger(Application::EVENT_AFTER_REQUEST);
        }

        // Catch any exceptions during handling
        catch (\Exception $e) {
            $craft->errorHandler->silentExitOnException = true;
            $craft->errorHandler->discardExistingOutput = false;
            $craft->errorHandler->handleException($e);
            $response = $craft->response;
        }

        return $response;
    }
}
