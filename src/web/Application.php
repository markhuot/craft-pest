<?php

namespace markhuot\craftpest\web;

use yii\web\Response;

/**
 * @property Request          $request
 * @property TestableResponse $response
 */
class Application extends \craft\web\Application
{
    public function handleRequest($request, bool $skipSpecialHandling = false): \markhuot\craftpest\web\TestableResponse
    {
        return parent::handleRequest($request, $skipSpecialHandling);
    }
}
