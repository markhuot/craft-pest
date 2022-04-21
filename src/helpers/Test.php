<?php

namespace markhuot\craftpest\helpers\test;

use Mockery;

if (!function_exists('mock')) {
    function mock($className) {
        $mock = Mockery::mock($className);
        \Craft::$container->set($className, $mock);
        return $mock;
    }
}

if (!function_exists('spy')) {
    function spy($className) {
        $spy = Mockery::spy($className);
        \Craft::$container->set($className, $spy);
        return $spy;
    }
}
