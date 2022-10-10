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
```php
$response->assertCookie('cookieName'); // checks presence, with any value
$response->assertCookie('cookieName', 'cookie value'); // checks that the values match
```

## assertCookieExpired()
Checks that the given cookie has an expiration in the past. Cookies are sent in headers and if left
unset a cookie will persist from request to request. Therefore, the only way to "remove" a cookie
is to set its expiration to a date in the past (negative number). This is common when logging people out.
```php
$response->assertCookieExpired('cookieName');
```

## assertCookieNotExpired()
Checks that the given cookie has an expiration in the future.
```php
$response->assertCookieNotExpired('cookieName');
```

## assertCookieMissing()
Checks that the given cookie is not present in the response
```php
$response->assertCookieMissing('cookieName');
```

## assertCreated()
Checks that the response has a 201 Created status code
```php
$response->assertCreated();
```

## assertDontSee()
Checks that the given string does not appear in thr response.
```php
$response->assertDontSee('text that should not be in the response');
```

## assertDontSeeText()
Checks that the given string does not appear in the response after first stripping all non-text elements (like HTML) from the response.
For example, if the response contains `foo <em>bar</em>` you could check against the text `foo bar` because the `<em>` will be stripped.
```php
$response->assertDontSeeText('foo bar');
```

## assertDownload()
Checks that the response contains a file download, optionally checking that the filename of the download
matches the given filename.
```php
$response->assertDownload(); // checks that any download is returned
$response->assertDownload('file.jpg'); // checks that a download with the name `file.jpg` is returned
```

## assertExactJson()
Checks that the given JSON exactly matches the returned JSON using PHPUnit's "canonicalizing" logic to
validate the objects.
```php
$response->assertExactJson(['foo' => 'bar']);
```

## assertForbidden()
Checks that the response has a 403 Forbidden status code
```php
$response->assertForbidden();
```

## assertHeader()
Checks that the given header is present in the response and, if provided, that the value of the
header matches the given value.
```php
$response->assertHeader('x-foo'); // checks for presence of header, with any value
$response->assertHeader('x-foo', 'bar'); // checks for header with matching value
```

## assertHeaderMissing()
Checks that the response headers do not contain the given header.
```php
$response->assertHeaderMissing('x-foo');
```

## assertLocation()
Checks that the location header matches the given location
```php
$response->assertLocation('/foo/bar');
```

## assertNoContent()
Check that the response has the given status code and no content.
```php
$response->assertNoContent();
```

## assertNotFound()
Check that the response returns a 404 Not Found status code
```php
$response->assertNotFound();
```

## assertOk()
Check that the response returns a 200 OK status code
```php
$response->assertOk();
```

## assertRedirect()
Check that the response returns a 300 status code
```php
$response->assertRedirect();
```

## assertRedirectTo()
A sugar method that checks the status code as well as the location of the redirect.
```php
$response->assertRedirectTo('/foo/bar');
```

## assertSee()
Checks that the response contains the given text
```php
$response->assertSee('foo bar');
```

## fill()
Fills any form data with a matching key with the given value.

## submit()
Submits a form matching the given selector