<?php

namespace markhuot\craftpest\helpers\http;

use markhuot\craftpest\web\TestableResponse;
use Pest\Expectation;

function get(string $uri='/'): TestableResponse {
    return test()->get($uri);
}

function expectGet($uri='/') {
    return test()->expect(fn () => test()->get($uri));
}
