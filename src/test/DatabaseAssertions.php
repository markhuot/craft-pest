<?php

namespace markhuot\craftpest\test;

use craft\base\ElementInterface;
use craft\db\Query;
use craft\db\Table;

/**
 * # Database Assertions
 *
 * You can assert that particular rows appear in the database using database assertions.
 */
trait DatabaseAssertions
{
    /**
     * Check that the given table contains the given number of rows.
     *
     * ```php
     * $this->assertDatabaseCount('{{%entries}}', 6);
     * ```
     */
    function assertDatabaseCount(string $tableName, int $expectedCount)
    {
        $actualCount = (new Query())->from($tableName)->count();

        $this->assertEquals($expectedCount, $actualCount);
    }

    /**
     * Check that the given table contains one or more matching rows
     * for the given condition.
     *
     * ```php
     * $this->assertDatabaseHas('{{%content}}', ['title' => 'My Great Title']);
     * ```
     */
    function assertDatabaseHas(string $tableName, array $condition)
    {
        $actualCount = (new Query)->from($tableName)->where($condition)->count();

        $this->assertGreaterThanOrEqual(1, $actualCount);
    }

    /**
     * Check that the given table contains zero matching rows
     * for the given condition.
     *
     * ```php
     * $this->assertDatabaseMissing('{{%content}}', ['title' => 'My Great Title']);
     * ```
     */
    function assertDatabaseMissing(string $tableName, array $condition)
    {
        $actualCount = (new Query)->from($tableName)->where($condition)->count();

        $this->assertSame(0, (int)$actualCount);
    }

    /**
     * Check that the given element has been trashed (soft deleted).
     *
     * ```php
     * $this->assertTrashed($entry);
     * ```
     */
    function assertTrashed(ElementInterface $element)
    {
        $row = (new Query)->from(Table::ELEMENTS)->where(['id' => $element->id])->one();

        $this->assertNotEmpty($row['dateDeleted']);
    }

    /**
     * Check that the given element has not been trashed (soft deleted).
     *
     * ```php
     * $this->assertNotTrashed($entry);
     * ```
     */
    function assertNotTrashed(ElementInterface $element)
    {
        $row = (new Query)->from(Table::ELEMENTS)->where(['id' => $element->id])->one();

        $this->assertEmpty($row['dateDeleted']);
    }
}
