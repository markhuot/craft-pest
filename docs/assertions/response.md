# Response Assertions
A testable response is returned whenever you perform a HTTP request
with Pest. It is an extension of Craft's native Response with a
number of convience methods added for testing. For example, most
tests will perform a `get()` and want to check that the response did
not return an error. You may use `->assertOk()` to check that the
status code was 200.

## getRequest()
Get the requesr that triggered this reaponse.

## querySelector(string $selector)
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

## form($selector = NULL)
The entry point for interactions with forms. This returns a testable
implementaion of the [Symfony DomCrawler's Form](#) class.

If a response only has one form you may call `->form()` without any parameters
to get the only form in the response. If the response contains more than
one form then you must pass in a selector matching a specific form.

To submit the form use `->submit()` or `->click('.button-selector')`.

## expectSelector(string $selector)
Runs the same `querySelector()` against the response's HTML but instead
of returning a `NodeList` it returns an expectation against the `NodeList`.
This allows you to use Pest's expectation API against the found nodes.

```php
$response->expectSelector('h1')->text->toBe('Hello World!');
```

## assertCookie(string $name, ?string $value = NULL)
Checks that the response contains the given cookie. When not passed a value
the assertion only checks the presence of the cookie. When passed a value the
value will be checked for strict equality.
```php
$response->assertCookie('cookieName'); // checks presence, with any value
$response->assertCookie('cookieName', 'cookie value'); // checks that the values match
```

## assertCookieExpired(string $name)
Checks that the given cookie has an expiration in the past. Cookies are sent in headers and if left
unset a cookie will persist from request to request. Therefore, the only way to "remove" a cookie
is to set its expiration to a date in the past (negative number). This is common when logging people out.
```php
$response->assertCookieExpired('cookieName');
```

## assertCookieNotExpired(string $name)
Checks that the given cookie has an expiration in the future.
```php
$response->assertCookieNotExpired('cookieName');
```

## assertCookieMissing(string $name)
Checks that the given cookie is not present in the response
```php
$response->assertCookieMissing('cookieName');
```

## assertCreated()
Checks that the response has a 201 Created status code
```php
$response->assertCreated();
```

## assertDontSee(string $text)
Checks that the given string does not appear in thr response.
```php
$response->assertDontSee('text that should not be in the response');
```

## assertDontSeeText(string $text)
Checks that the given string does not appear in the response after first stripping all non-text elements (like HTML) from the response.
For example, if the response contains `foo <em>bar</em>` you could check against the text `foo bar` because the `<em>` will be stripped.
```php
$response->assertDontSeeText('foo bar');
```

## assertDownload(?string $filename = NULL)
Checks that the response contains a file download, optionally checking that the filename of the download
matches the given filename.
```php
$response->assertDownload(); // checks that any download is returned
$response->assertDownload('file.jpg'); // checks that a download with the name `file.jpg` is returned
```

## assertExactJson(array $json)
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

## assertHeader(string $name, ?string $expected = NULL)
Checks that the given header is present in the response and, if provided, that the value of the
header matches the given value.
```php
$response->assertHeader('x-foo'); // checks for presence of header, with any value
$response->assertHeader('x-foo', 'bar'); // checks for header with matching value
```

## assertHeaderMissing(string $name)
Checks that the response headers do not contain the given header.
```php
$response->assertHeaderMissing('x-foo');
```

## assertLocation(string $location)
Checks that the location header matches the given location
```php
$response->assertLocation('/foo/bar');
```

## assertFlash(?string $message = NULL, ?string $key = NULL)
Check that the given message/key is present in the flashed data.

```php
$response->assertFlash('The title is required');
$response->assertFlash('Field is required', 'title');
```

## assertNoContent($status = 204)
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

## assertRedirectTo(string $location)
A sugar method that checks the status code as well as the location of the redirect.
```php
$response->assertRedirectTo('/foo/bar');
```

## followRedirect()
For a 300 class response with a `Location` header, trigger a new
request for the redirected page.

## followRedirects()
For a 300 class response with a `Location` header, trigger a new
request for the redirected page.

## assertSee(string $text)
Checks that the response contains the given text
```php
$response->assertSee('foo bar');
```