<?php

namespace markhuot\craftpest\helpers\http;

function get($uri='/') {
    return test()->get($uri);
}
