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
    /**
     * Example description.
     */
    public function get(string $uri=null): \markhuot\craftpest\test\Response
    {
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

        $craft->trigger(Application::EVENT_BEFORE_REQUEST);

        // Run the application
        try {
            $originalRequest = $craft->request;
            $craft->setComponents(['request' => $request]);
            $response = $craft->handleRequest($request);
            $craft->setComponents(['request' => $originalRequest]);
        }

            // Catch any exceptions during handling
        catch (\Exception $e) {
            $craft->errorHandler->silentExitOnException = true;
            $craft->errorHandler->discardExistingOutput = false;
            $craft->errorHandler->handleException($e);
            $response = $craft->response;
        }

        $craft->trigger(Application::EVENT_AFTER_REQUEST);
        return $response;
    }
}
