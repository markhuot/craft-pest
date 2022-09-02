<?php

namespace markhuot\craftpest\helpers\http;

use markhuot\craftpest\web\TestableResponse;
use Pest\Expectation;

/**
 * @return TestableResponse
 */
function get(string $uri='/') {
    return test()->get($uri);
}

function expectGet($uri='/') {
    return test()->expect(fn () => test()->get($uri));
}
