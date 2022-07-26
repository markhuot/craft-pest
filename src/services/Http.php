<?php

namespace markhuot\craftpest\services;

use craft\console\Application;
use craft\helpers\App;
use craft\web\Application as WebApplication;
use craft\web\Response;
use craft\web\TemplateResponseBehavior;
use craft\web\TemplateResponseFormatter;
use craft\web\UrlManager;
use GuzzleHttp\Psr7\Message;
use markhuot\craftpest\behaviors\TestableResponseBehavior;
use markhuot\craftpest\web\Request;
use Symfony\Component\Process\Process;
use yii\base\Event;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class Http
{
    /**
     * Preform get request and return a response
     * with helper methods to allow assertions on it
     *
     * @see TestableResponseBehavior
     */
    public function get(string $uri=null, $cookies = []): \markhuot\craftpest\web\Response
    {
        $request = Request::createGetRequestFromUri($uri);
        $request->cookies->fromArray($cookies);

        /** @var WebApplication $craft */
        $craft = \Craft::$app;

        $craft->set('request', $request);
        $craft->set('response', \markhuot\craftpest\web\Response::class);
        $craft->getView()->setTemplateMode('site');

        if ($cookies) {
            $craft->getView()->setTemplateMode('cp');
        }

        $craft->setComponents([
            'request' => $request,
            'response' => $craft->get('response'),

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

        // Run the application
        try {

            $craft->trigger(Application::EVENT_BEFORE_REQUEST);
            /** @var \craft\web\Response $response */
            $response = $craft->handleRequest($request, true);
            $response->prepare();
            $response->attachBehavior('testableResponse', TestableResponseBehavior::class);

            if($cookies) {
                dd($response);
            }
            $craft->trigger(Application::EVENT_AFTER_REQUEST);
        }

        // Catch any exceptions during handling
        catch (\Exception $e) {

            // Support for status code checks
            if (is_a($e, HttpException::class)) {

                if($cookies) {
                    dd($e);
                }

                $response = \Craft::createObject(\markhuot\craftpest\web\Response::class);
                $response->setStatusCode($e->statusCode);

                // Error response
                return $response;
            }

            // Something unexpected 
            echo $e->getMessage();
            echo $e->getTraceAsString();
            die;
        }

        // Successful response
        return $response;
    }



}
