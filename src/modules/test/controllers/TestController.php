<?php

namespace markhuot\craftpest\modules\test\controllers;

use craft\web\Controller;
use yii\web\Cookie;

class TestController extends Controller
{
    function actionTestableWebResponse()
    {
        return $this->asJson(['foo' => 'bar']);
    }

    function actionTestableWebAction()
    {
        $this->requirePostRequest();

        return $this->asJson(['foo' => 'bar']);
    }

    function actionCookieIncrement()
    {
        $this->requirePostRequest();

        if ($cookie = \Craft::$app->getRequest()->getCookies()->get('foo')) {
            $cookie->value++;
        } else {
            $cookie = new Cookie([
                'name' => 'foo',
                'value'=> 0,
                'expire' => 100
            ]);
        }
        \Craft::$app->getResponse()->getCookies()->readOnly = false;
        \Craft::$app->getResponse()->getCookies()->add($cookie);

        return $this->asJson(['counter' => $cookie->value]);
    }

    function actionSession()
    {
        $this->requirePostRequest();

        $session = \Craft::$app->getSession()->get('foo', 0);
        $session++;

        \Craft::$app->getSession()->set('foo', $session);

        return $this->asJson([$session]);
    }
}
