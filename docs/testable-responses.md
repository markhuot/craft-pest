A testable response is returned whenever you perform a HTTP request
with Pest. It is an extension of Craft's native Response with a
number of convience methods added for testing. For example, most
tests will perform a `get()` and want to check that the response did
not return an error. You may use `->assertOk()` to check that the
status code was 200.

## querySelector()
If the response returns HTML you can `querySelector()` to inspect the
HTML for specific content. The `querySelector()` method takes a
CSS selector to look for (just like in Javascript).

The return from `querySelector()` is always a `NodeList` containing zero
or more nodes. You can interact with the `NodeList` regardless of the return
and you will get back a scalar value or a collection of values.

```php
$response->querySelector('h1')->text; // returns the string contents of the h1 element
$response->querySelector('li')->text; // returns a collection containing the text of all list items
```

## expectSelector()
Runs the same `querySelector()` against the response's HTML but instead
of returning a `NodeList` it returns an expectation against the `NodeList`.
This allows you to use Pest's expectation API against the found nodes.

```php
$response->expectSelector('h1')->text->toBe('Hello World!');
```

## expect()
Starts an expectation on the response. This allows you to use the expectation
API on Craft's response properties.
```php
$response->expect()->statusCode->toBe(200);
```

## assertCookie()
Checks that the response contains the given cookie. When not passed a value
the assertion only checks the presence of the cookie. When passed a value the
value will be checked for strict equality.

## assertNoContent()
Assert that the response has the given status code and no content.