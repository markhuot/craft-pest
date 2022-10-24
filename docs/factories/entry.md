Entry Factory

You can easily build entries using the Entry factory.

## section($identifier)
Set the section for the entry to be created. You may pass a section
in three ways,

1. a section object (typically after creating one via the `Section` factory)
2. a section id
3. a section handle

If you do not pass a section, one will be created automatically.

## type($handle)
Set the entry type

## postDate(DateTime|string|int $value)
Set the post date by passing a `DateTime`, a string representing the date like
"2022-04-25 04:00:00", or a unix timestamp as an integer.

## expiryDate(DateTime|string|int $value)
Set the expiration date by passing a `DateTime`, a string representing the date like
"2022-04-25 04:00:00", or a unix timestamp as an integer.

## setDateField($key, $value)
Date fields in Craft require a `DateTime` object.  You can use `->setDateField` to pass
in other representations such as a timestamp or a string.

```php
Entry::factory()->setDateField('approvedOn', '2022-04-18 -04:00:00');
Entry::factory()->setDateField('approvedOn', 1665864918);
```

## author(craft\web\User|string|int $user)
Set the author of the entry. You may pass a full user object, a user ID,
a username, email, or a user ID.