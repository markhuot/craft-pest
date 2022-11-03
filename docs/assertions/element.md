# Elements

Elements, like entries, and be tested in Craft via the following assertions.

## assertValid(array $keys = array ())
Asserts that the element is valid (contains no errors from validation).

> **Note**
> Since validation errors throw Exceptions in Pest, by default, you must
> silence those exceptions to continue the test.

```php
Entry::factory()
  ->create()
  ->assertValid()
```

## assertInvalid(array $keys = array ())
Asserts that the element is invalid (contains errors from validation).

```php
Entry::factory()
  ->muteValidationErrors()
  ->create(['title' => null])
  ->assertInvalid();
```

## assertTrashed()
Check that the element has its `dateDeleted` flag set

```php
$entry = Entry::factory()->create();
\Craft::$app->elements->deleteElement($entry);
$entry->assertTrashed();
```

## assertNotTrashed()
Check that the element does not have its `dateDeleted` flag set

```php
Entry::factory()->create()->assertNotTrashed();
```