<?php

namespace markhuot\craftpest\helpers\http;

use Pest\Expectation;

function get(string $uri='/') {
    return test()->get($uri);
}

function expectGet($uri='/') {
    return test()->expect(fn () => test()->get($uri));
}
