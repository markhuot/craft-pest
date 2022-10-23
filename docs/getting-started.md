# Writing your first test

Pest has two ways to write tests. Each has its own advantages.

## Classic

The safest and most standard way to write a test is to enclose the test within a `test` or `it` function (they do the same thing, pick the one that reads the best when spoken out loud). Using the classic approach gives you access to a closure that runs within Craft's application context. For example,

```php
test('the homepage loads', function() {
  // ....
});

it('loads the homepage', function() {
  // ...
});
```

Within the closure you should perform your test and assert the result. In other words, do a task your website does and then make sure the result of that thing is what you expect. The easiest place to start is to load a page via the `->get()` method.

```php
it('loads the homepage', function() {
  $this->get('/')->assertOk();
});
```

The `->get()` method will query your site in testing the same way a user would through a web browser (without executing any JavaScript). It returns a `TestableResponse` that contains a number of convenience methods allowing you to verify various parts of the response. Commonly you'll want to `->assertOk()` to ensure you got a successful 200 response back from the server.

For a full list of the assertions you can make on a `TestableResponse` see the [response.md](assertions/response.md) doc.

## Higher Order Tests

Pest allows for tests to be written without a closure as well. They call this [Higher Order Tests](https://pestphp.com/docs/higher-order-tests) and they look like this,

```php
it('loads the homepage')
  ->get('/')
  ->assertOk()
  ->assertSee('Welcome!');
```

Writing tests this way is a bit more complex because there is no closure providing the application context. Because this style test is read before Craft even starts up, you can't use the `\Craft::$app` singleton or any factory methods.

For testing a seeded database that already contains your site content, though, this can greatly simplify test writing.

## Navigating

You can make multiple requests to Craft in a single test. One way is to write multiple `->get()` calls passing a new page each time.

Alternatively, Pest encourages method chaining to create a fluent interface expressing multiple steps in a sentence like structure. The most used methods to do this are `->querySelector` and `->click`/`->submit`.

With a response you may query down to a link, click that link, fill a form, and submit the form.

```php
$this->get('/')
  ->querySelector('.buy_now_btn')
  ->click()
  ->fill('qty', 10)
  ->submit()
  ->assertSee('Thank you');
```

## What to Test

If you're just getting started with testing or just getting started with a new project one of the easiest places to start is to add a single test ensuring the homepage loads without errors.

```php
it('loads the homepage')->get('/')->assertOk();
```

Beyond that, a general rule of thumb is that you should write a test for any piece of code that,

1. Is called/repeated/a dependency of multiple places
2. Reflects a specific business need/decision
3. Extends Craft's native feature set

We can look at each of those rules in specific examples,

### Dependency management

If your templates pass a lot of variables around they may be an excellent candidate for testing. Say you have a `blogpost-card.twig` template that shows a blog post in a variety of contexts. You might use it in the main column of the website to show a teaser linking to the post or you might use it in the sidebar/footer to show a shorter version of the link. Because `blogpost-card.twig` is used in multiple places we need a test to ensure we're using it in the correct way in each place.

You can use Craft Pest's `->querySelector()` to help. For example, if the larger context view shows author information you may query for authors to ensure they are present in the `main` element.

```php
it('renders big blog posts')
  ->get('/blog')
  ->assertOk()
  ->querySelector('main .blog__author')
  ->assertSee('By Michael Bluth');
```

If author information should be hidden when used in a sidebar you could assert that in a similar way,

```php
it('renders sidebar blog posts')
  ->get('/blog')
  ->assertOk()
  ->querySelector('aside .blog__author')
  ->assertDontSee('By Michael Bluth');
```

By including these two tests we now have confidence that we are correctly hiding and showing author information based on the use case. We're confident in the `/blog` page _and_ we're confident in the `blogpost-card.twig` template.

Most times you pass a variable to `{% include "template" with { variable: ... } %}` you'll want to write a test for that `variable`.

### Specific business need/decision

Say you're building a resource center and documents in this resource center have the ability to be "archived" via a lightswitch field on the entry type. When a resource is archived you want to hide it from the resource center unless a user checks the "Show archived" checkbox on the front-end. 

This is an example of a business need and our test needs to test that the lightswitch field does what we expect it to do. Without getting in to [factories](factories.md), you could test this logic via something like this,

```php
it('hides archived resources')
  ->get('/resources')
  ->assertOk()
  ->assertDontSee('My Archived Resource');
```

This assumes that you have a resource in the database called "My Archived Resource" that is already saved as "Archived."

This test gives us confidence tha the "Archived" lightswitch is doing what we expect it to do. We should then repeat this test for other custom fields in Craft that affect the functionality of the site.

### Extending Craft

If you're custom building a plugin or adding filters/functions to Twig then a test is a great way to ensure that functionality continues working as expected.

## Testing the unnecessary 

It is common to want to write tests for every custom field in Craft. But that might not be the best use of your time. Extending the "resource center" example from above, assume that resources have a Categories field that allows a resource to have one or more categories. You _could_ write a test that loads a resource and confirms the category is displayed on the detail page. It might look something like this,

```php
it('shows resource categories')
  ->get('/resources/my-archived-resource')
  ->assertOk()
  ->assertSee('Category One')
  ->assertSee('Category Two');
```

That test will pass, assuming the resource is tied to the noted categories. But, what are we really testing there? We're testing our Twig template for the presence of `craft.categories.relatedTo(resource)`. That's not code that we control, per-se. The functionality of `.relatedTo()` and its ability to pull categories on to the page is a native Craft feature that Pixel and Tonic tests for you. We have a high degree of confidence that the raw functionality of pulling related elements will always work. So, if we ignore the functionality of `.relatedTo()` our test is actually just a duplication of the Twig logic. We've duplicated our logic in to two places now making future updates more brittle. If your custom field is only used in one place or only seen in one template it may not need a test. The act of loading the page may be test-enough. We could therefore rewrite the above test as,

```php
it('shows resource detail pages')
  ->get('/resources/my-archived-resource')
  ->assertOk()
```

Now we know the page loaded and loaded without any errors. Because (in this example) categories are only used in that one place, we have a high degree of confidence they're included on the page too since we're leaning on Craft's native functionality to pull them in.

Admittedly, this is a very ambiguous line in the sand for what constitutes a test. If categories had _any_ additional logic on them like ordering by a "priority" field or only showing the first two categories (even if five are assigned) then we might need a test here. But, both of those additional pieces of logic add additional logic outside of the native Craft functionality. Our standard `.relatedTo()` query has just become more involved and now includes custom functionality that is unique to our site. We now need a test for that custom functionality.
