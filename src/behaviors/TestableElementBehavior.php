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
        expect($this->owner->errors)->toHaveCount(0);
    }

    function assertInvalid(array $keys = [])
    {
        expect(count($this->owner->errors))->toBeGreaterThanOrEqual(1);
    }
}