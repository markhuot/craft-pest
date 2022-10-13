# Expectations

Pest provides a fantastic [expectation API](https://pestphp.com/docs/expectations) that you can use in place of the more traditional `->assert...` methods. In vanilla Pest projects they look like this,

```php
expect($something)->toBe($somethingElse);
```

When you call `expect()` it returns an "expectable object" that you can chain your expectations on to. The most common expectation is `->toBe()` and it checks that the thing your "expecting" is the same as the thing it should "be."

In Craft you could expect that a response is `200 Ok` like this,

```php
expect($response)->statusCode->toBe(200);
```

You can also chain multiple expectations and each one will execute in order. For example, we could check the `statusCode` as well as the `contentType` to ensure we're getting back HTML.

```php
expect($response)
    ->statusCode->toBe(200)
    ->contentType->toBe('text/html');
```

It's important to note that each time you call an expectation's `->to...` method the chain resets back to the original expectation. Here's a slightly more contrived example to illustrate that,

```php
expect($person)
    ->firstName->toBe('Michael')
    ->lastName->toBe('Bluth')
    ->brothers->toHaveCount(2)
    ->brothers[0]->firstName->toBe('Gob')
    ->father->firstName->toBe('George')
    ->mother->firstName->toBe('Lucille');
```

Note the nesting above, even though we're calling `$person->brothers[0]->firstName`, after we call `->toBe()` the chain is reset and the next expectation reverts back to the original `$person`.

You can use this feature to start an expectation on a response and from there assert several values and DOM nodes all at the same time. For example,

```php
expect($response)
    ->statusCode->toBe(200)
    ->querySelector('h1')->text->toBe('Hello World!')
    ->querySelector('.news__item')->count->toBe(5)
    ->querySelector('.person__name')->text->toBe(['Michael', 'Gob']);
```
