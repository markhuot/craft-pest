<?php

use function markhuot\craftpest\helpers\http\get;

beforeEach(function () {
    \Craft::setAlias('@templates', __DIR__ . '/templates');
});

get('/')->assertOk();
get('/?foo=1')->assertOk();
