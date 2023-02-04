# Database Assertions
You can assert that particular rows appear in the database using database assertions.

## assertDatabaseCount(string $tableName, int $expectedCount)
Check that the given table contains the given number of rows.
```php
$this->assertDatabaseCount('{{%entries}}', 6);
```

## assertDatabaseHas(string $tableName, array $condition)
Check that the given table contains one or more matching rows
for the given condition.
```php
$this->assertDatabaseHas('{{%content}}', ['title' => 'My Great Title']);
```

## assertDatabaseMissing(string $tableName, array $condition)
Check that the given table contains zero matching rows
for the given condition.
```php
$this->assertDatabaseMissing('{{%content}}', ['title' => 'My Great Title']);
```

## assertTrashed(craft\base\Element $element)
Check that the given element has been trashed (soft deleted).
```php
$this->assertTrashed($entry);
```

## assertNotTrashed(craft\base\Element $element)
Check that the given element has not been trashed (soft deleted).
```php
$this->assertNotTrashed($entry);
```