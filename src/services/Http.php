<?php

namespace markhuot\craftpest\services;

use craft\helpers\App;
use craft\web\Application;
use craft\web\Response;
use craft\web\UrlManager;
use GuzzleHttp\Psr7\Message;
use markhuot\craftpest\behaviors\TestableResponseBehavior;
use markhuot\craftpest\web\Request;
use Symfony\Component\Process\Process;
use yii\base\Event;

class Http
{
    /**
     * Example description.
     */
    public function get(string $uri=null): \craft\web\Response
    {
        $uri = ltrim($uri, '/');

        $config = App::webRequestConfig();
        $config['class'] = \markhuot\craftpest\web\Request::class;
        $request = \Craft::createObject($config)->setRaw([
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
            $response = $craft->response;
            $response->attachBehavior('testableResponse', TestableResponseBehavior::class);

            // $response = (new \markhuot\craftpest\test\Response);
            // $response->attachBehavior('testableResponse', TestableResponseBehavior::class);
            // foreach ($craft->response->getBehaviors() as $key => $value) {
            //     $response->attachBehavior($key, $value);
            // }

            $craft->setComponents([
                'request' => $request,
                'response' => $response,

                // Since we just modified the request on demand a lot of Craft's native assumptions
                // are out of date. Craft works off a request/response paradigm and by sending
                // multiple requests through a single instance of the Craft application it can get
                // confused.
                // We'll help out by resetting a few components (causing them to recalculate their
                // internal state). The config here is no different than the default config.
                'urlManager' => [
                    'class' => \craft\web\UrlManager::class,
                    'enablePrettyUrl' => true,
                    'ruleConfig' => ['class' => \craft\web\UrlRule::class],
                ],
            ]);

            $craft->view->setTemplateMode('site');

            $craft->trigger(Application::EVENT_BEFORE_REQUEST);
            $response = $craft->handleRequest($request, true);
            $craft->trigger(Application::EVENT_AFTER_REQUEST);
        }

        // Catch any exceptions during handling
        catch (\Exception $e) {
            // Native Craft error handling
            // $craft->errorHandler->silentExitOnException = true;
            // $craft->errorHandler->discardExistingOutput = false;
            // $craft->errorHandler->handleException($e);
            // $response = $craft->response;

            // Add in ability to "expect" exceptions so this gets passed over
            // Should be able to $this->expect(PageNotFoundException::class)->get('/foo') or something like that
            echo $e->getMessage();
            echo $e->getTraceAsString();
            die;
        }

        return $response;
    }
}
