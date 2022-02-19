<?php

namespace markhuot\craftpest\helpers\http;

use markhuot\craftpest\test\Response;

function get($uri='/'): Response {
    return test()->get($uri);
}
