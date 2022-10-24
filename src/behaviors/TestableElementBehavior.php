<?php

namespace markhuot\craftpest\behaviors;

use yii\base\Behavior;

/**
 * @property \craft\base\Element $owner
 */
class TestableElementBehavior extends Behavior
{
    function assertValid(array $keys = [])
    {
        test()->assertCount(0, $this->owner->errors);
    }

    function assertInvalid(array $keys = [])
    {
        test()->assertGreaterThanOrEqual(1, count($this->owner->errors));
    }
}
