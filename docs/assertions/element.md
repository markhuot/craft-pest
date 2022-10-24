# Elements

Elements, like entries, and be tested in Craft via the following assertions.

## assertValid(array $keys = array ())
Asserts that the element is valid (contains no errors from validation).

Note: since validation errors throw Exceptions in Pest, by default, you must
silence those exceptions to continue the test.

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