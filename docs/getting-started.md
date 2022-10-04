# Writing your first test

Pest has three ways to write tests. Each has its own advantages.

## Classic

The safest and most standard way to write a test is to enclose the test within a `test` or `it` function (they do the same thing, pick the one that reads the best when spoken out loud). Using the classic approach gives you access to a closure that runs within the application context. For example,

```php
test('the homepage loads', function() {
  // ....
});

it('loads the homepage', function() {
  // ...
});
```

Within the closure you should perform your test and assert the result. In other words, do a task your website does and then make sure the result of that thing is what you expect. The easiest thing to do is load a page via the `->get()` method.

```php
it('loads the homepage', function() {
  $this->get('/')->assertOk();
});
```

The `->get()` method will query your site in testing the same way a user would through a web browser. It returns a `TestableResponse` that contains a number of convienence methods allowing you to verify various parts of the response. Commonly you'll want to `->assertOk()` to endure you got a successful 200 response back from the server.