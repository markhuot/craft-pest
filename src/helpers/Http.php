<?php

namespace markhuot\craftpest\helpers\http;

use markhuot\craftpest\web\TestableResponse;
use Pest\PendingObjects\TestCall;

/**
 * @return TestableResponse|TestCall
 */
function get(string $uri='/') {
    return test()->get($uri);
}

function expectGet($uri='/') {
    return test()->expect(function () use ($uri) {
        return test()->get($uri);
    });
}
