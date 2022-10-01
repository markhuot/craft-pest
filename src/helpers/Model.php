<?php

namespace markhuot\craftpest\helpers\model;

use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\Factory;
use markhuot\craftpest\factories\User;
use Pest\Support\HigherOrderTapProxy;

if (!function_exists('entry')) {
    function entry(string $handle) {
        return Entry::factory()->section($handle);
        // return test(null, fn () => Entry::factory()->section($sectionHandle));
        // return new HigherOrderTapProxy();

        // return test()->entry(...$args);
        // return test()->tap(fn () => Entry::factory()->section($args[0]));
        // return test()->then('entry', ...$args);
    }
}

if (!function_exists('user')) {
    function user() {
        return User::factory();
    }
}
