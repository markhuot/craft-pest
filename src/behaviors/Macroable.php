<?php

namespace markhuot\craftpest\behaviors;

use yii\base\Behavior;
use yii\base\InvalidCallException;
use yii\base\UnknownMethodException;


class Macroable extends Behavior
{
    static array $macros = [];
    
    public static function macro($class, $name, callable $callback)
    {
        if (!isset(static::$macros[$class])) {
            static::$macros[$class] = [];
        }
        
        static::$macros[$class][$name] = $callback;
    }
    
    function hasMethod($method)
    {
        return isset(static::$macros[get_class($this->owner)][$method]);
    }
    
    function __call($method, $args=[]) {
        if (!$this->hasMethod($method)) {
            throw new UnknownMethodException("Unknown method $method()");
        }

        $callback = static::$macros[get_class($this->owner)][$method];

        return $callback($this->owner, ...$args);
    }
}
