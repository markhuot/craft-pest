<?php

namespace markhuot\craftpest\behaviors;

use yii\base\Behavior;


class Macroable extends Behavior
{
    static $macros = [];
    
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
        return static::$macros[get_class($this->owner)][$method]($this->owner, ...$args);
    }
}