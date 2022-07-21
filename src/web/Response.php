<?php

namespace markhuot\craftpest\web;

use markhuot\craftpest\behaviors\TestableResponseBehavior;

class Response extends \craft\web\Response
{
    public function behaviors()
    {
        return [TestableResponseBehavior::class];
    }

    public function prepare(): void
    {
        parent::prepare();
    }
}
