<?php

use function markhuot\craftpest\helpers\http\get;

it ('does not run a conditional', function () {
    get('/')->assertOk()->assertDontSee('getParam');
});

it ('runs a conditional via a query var', function () {
    get('/?foo=1')->assertOk()->assertSee('getParam');
});

get('/loop-test')
    ->assertOk();
