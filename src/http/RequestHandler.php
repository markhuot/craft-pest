<?php

namespace markhuot\craftpest\http;

use markhuot\craftpest\web\TestableResponse;
use Twig\Error\RuntimeError;
use yii\base\ExitException;
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
        $obLevel = ob_get_level();
        $this->registerWithCraft($request);

        try {
            $this->app->trigger(\craft\web\Application::EVENT_BEFORE_REQUEST);

            // The actual call
            /** @var TestableResponse $response */
            $response = $this->app->handleRequest($request, $skipSpecialHandling);
            $response->setRequest($request);
            $response->prepare();

            test()->storeCookieCollection($response->cookies);

            $this->app->trigger(\craft\web\Application::EVENT_AFTER_REQUEST);

            return $response;
        }

        // If it's a response that normally returns HTML, then suppress the error and return
        // HTML with the appropriate status code
        catch (HttpException $e) {
            // Fake a response and set the HTTP status code
            $response = \Craft::createObject(\markhuot\craftpest\web\TestableResponse::class);
            $response->setStatusCode($e->statusCode);
            $response->setRequest($request);

            // Error response
            return $response;
        }

        // Twig is _so_ annoying that it wraps actual exceptions. So we need to catch _all_ twig
        // exceptions and unpack them to see what the underlying exception was and then handle
        // it here. Unfortunately, this is duplicated because if the exception is thrown outside
        // of twig then the normal `catch` block will catch it too.
        catch (RuntimeError $e) {
            if (is_a($e->getPrevious(), ExitException::class)) {
                /** @var TestableResponse */
                return \Craft::$app->response;
            }

            throw $e;
        }
        catch (ExitException $e) {
            /** @var TestableResponse */
            return \Craft::$app->response;
        }

        // Clear out output buffering that may still be left open because of an exception. Ideally
        // we wouldn't need this but Yii/Craft leaves something open somewhere that we're not
        // handling correctly here.
        finally {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
        }
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
    }
}
