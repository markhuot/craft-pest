<?php

namespace markhuot\craftpest\http;

use markhuot\craftpest\web\Application;
use markhuot\craftpest\web\TestableResponse;
use yii\web\HttpException;

class RequestHandler
{
    private \craft\web\Application $app;

    public function __construct(?\craft\web\Application $app = null)
    {
        $this->app = $app ?? \Craft::$app;
    }

    public function handle($request, $skipSpecialHandling = false): TestableResponse
    {
        $this->registerWithCraft($request);

        try {

            $this->app->trigger(Application::EVENT_BEFORE_REQUEST);

            // The actual call
            /** @var TestableResponse $response */
            $response = $this->app->handleRequest($request, $skipSpecialHandling);
            $response->prepare();

            $this->app->trigger(Application::EVENT_AFTER_REQUEST);
        }

        // Catch any exceptions during handling
        catch (\Exception $e) {

            if (is_a($e, HttpException::class)) {
                // Fake a response and set the HTTP status code
                $response = \Craft::createObject(\markhuot\craftpest\web\TestableResponse::class);
                $response->setStatusCode($e->statusCode);

                // Error response
                return $response;
            }

            // Something unexpected
            echo $e->getMessage();
            echo $e->getTraceAsString();
            die;
        }

        return $response;
    }

    private function registerWithCraft($request): void
    {
        $this->app->getConfig()->getGeneral()->runQueueAutomatically = false;
        $this->app->getView()->setTemplateMode($request->isCpRequest ? 'cp' : 'site');

        // The next request
        $this->app->set('request', $request);

        // A response object with methods for assertions
        // Yii will fill it with data once the response is successful
        $response = \Craft::createObject(\markhuot\craftpest\web\TestableResponse::class);

        // Copy over any behaviors from the original response
        $response->attachBehaviors($this->app->response->behaviors);

        // Set the new response in the container
        $this->app->set('response', $response);

        $this->app->setComponents([
            'request' => $request,
            'response' => $this->app->get('response'),

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
    }
}
