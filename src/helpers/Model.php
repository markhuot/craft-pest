<?php

namespace markhuot\craftpest\helpers\model;

use markhuot\craftpest\factories\Entry;

if (!function_exists('factory')) {
    function factory($sectionHandle, $definition) {
        return Entry::factory()
            ->section($sectionHandle)
            ->define($definition);
    }
}
