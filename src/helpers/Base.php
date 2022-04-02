<?php

namespace markhuot\craftpest\helpers\model;

use Illuminate\Support\Collection;

if (!function_exists('collectOrCollection')) {
    function collectOrCollection($value) {
        return is_a($value, Collection::class) ? $value : collect()->push($value);
    }
}
