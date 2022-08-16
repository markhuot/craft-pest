<?php

namespace markhuot\craftpest\helpers\http;

use \craft\web\Response;
use markhuot\craftpest\behaviors\TestableResponseBehavior;

/**
 * @param string $uri
 *
 * @return Response | TestableResponseBehavior
 */
function get($uri='/') {
    return test()->get($uri);
}

function expectGet($uri='/') {
    return test()->expect(fn () => $this->get($uri));
}
