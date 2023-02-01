<?php

namespace markhuot\craftpest\behaviors;

use craft\elements\db\ElementQuery;
use yii\base\Behavior;

/**
 * # Element Queries
 *
 * Element queries have been extended with a few assertions to make testing easier
 *
 * @property ElementQuery $owner
 */
class TestableElementQueryBehavior extends Behavior
{
    /**
     * Asserts that the count of the query matches the expectation
     *
     * ```php
     * Entry::find()
     *   ->section('jobs')
     *   ->assertCount(10)
     * ```
     */
    function assertCount(int $count)
    {
        $actualCount = $this->owner->count();

        if (is_numeric($actualCount)) {
            $actualCount = (int)$actualCount;
        }

        expect($count)->toBe($actualCount);

        return $this->owner;
    }
}
