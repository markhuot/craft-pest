<?php

namespace markhuot\craftpest\behaviors;

use yii\base\Behavior;

class ExpectableBehavior extends Behavior
{
    function expect()
    {
        return test()->expect($this->owner);
    }

    // function __isset($name)
    // {
    //     return $name === 'expect';
    // }
    //
    // function __get($name)
    // {
    //     if ($name === 'expect') {
    //         return test()->expect($this->owner);
    //     }
    //
    //     throw new \InvalidArgumentException("Could not find {$name} on ".get_class($this->owner));
    // }
}
