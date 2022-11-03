<?php

namespace markhuot\craftpest\modules\test\controllers;

use craft\web\Controller;

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
}
