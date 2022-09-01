<?php

namespace markhuot\craftpest\helpers\http;

use markhuot\craftpest\web\TestableResponse;
use Pest\PendingObjects\TestCall;

function get(string $uri='/'): TestableResponse|TestCall {
    return test()->get($uri);
}

function expectGet($uri='/') {
    return test()->expect(fn () => test()->get($uri));
}
