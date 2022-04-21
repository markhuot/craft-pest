<?php

namespace markhuot\craftpest\helpers\base;

use Illuminate\Support\Collection;

if (!function_exists('collectOrCollection')) {
    function collectOrCollection($value) {
        return is_a($value, Collection::class) ? $value : collect()->push($value);
    }
}
