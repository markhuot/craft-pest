<?php

namespace markhuot\craftpest\helpers\http;

use markhuot\craftpest\test\Response;
use Pest\Expectation;

/**
 * @param string $uri
 *
 * @return Response
 */
function get($uri='/') {
    return test()->get($uri);
}

function expectGet($uri='/') {
    return test()->expect(fn () => test()->get($uri));
}
