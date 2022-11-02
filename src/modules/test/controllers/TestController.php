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

        if ($cookie = \Craft::$app->getRequest()->getCookies()->get('c')) {
            $cookie->value++;
        } else {
            $cookie = new Cookie([
                'name' => 'c',
                'value'=> 0,
                'expire' => 100
            ]);
        }
        \Craft::$app->getResponse()->getCookies()->readOnly = false;
        \Craft::$app->getResponse()->getCookies()->add($cookie);

        return $this->asJson(['counter' => $cookie->value]);
    }
}
