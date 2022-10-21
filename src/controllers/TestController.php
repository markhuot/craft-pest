<?php

namespace markhuot\craftpest\controllers;

use craft\web\Controller;

class TestController extends Controller
{
    function actionTestableWebResponse()
    {
        return $this->asJson(['foo' => 'bar']);
    }
}