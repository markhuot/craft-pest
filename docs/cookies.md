# Cookies

Craft-pest simulates a browser's cookie storage throughout a single test. That
means that cookies are retained through multiple requests in a single test.

For example, the following may set a logged in cookie on the first request.
That cookie will then be retained through the subsequent requests.

```php
it ('logs a user in and navigates their dashboard', function () {
  $this->get('/login')
    ->fill('username', 'michael')
    ->fill('password', '***')
    ->submit()
    ->assertOk();

  $this->get('/dashboard')->assertOk();
  $this->get('/dashboard/secret-page')->assertOk();
  $this->get('/dashboard/dangerous-page')->assertOk();
});
```

> **Note**
> This is a verbose and slow way to manage login. It's better to use the
> `->actingAs()` method on a test to log a user in.

## clearCookieCollection()
If you need to clear the stored cookies mid-test you can call
`test()->clearCookieCollection()`.

## storeCookieCollection(?yii\web\CookieCollection $cookies)
Stores cookies from the passed cookie collection in the test state
so they can be re-sent to subsequent requests in the same test.

> *Warning* This is automatically called after every response so that cookies
may be retained through a test. Expired cookies that come back from
a test are automatically pruned, mimicing the functionality of
a browser.

## getCookieCollection()
Get the stored cookie collection