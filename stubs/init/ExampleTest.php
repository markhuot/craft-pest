<?php

use function markhuot\craftpest\helpers\http\get;

//
// "Better to light one candle than to curse the darkness." ~ Chinese Proverb
//
// Your test library will grow over time and become more and more complex with each added feature. You will add
// business logic, unwritten rules, and all sorts of odds and ends here. Pest makes it all so easy!
//
// Before any of that, though, let's start with a simple test that the homepage loads and
// returns an 200 Ok status code.
get('/')->assertOk();

// Same thing, different syntax
it('renders home page without errors', function () {
    $this->get('/')->assertOk();
});

// Or a 404
get('/non-existing-page')->assertNotFound();

// Same thing, different syntax
it('returns a 404 on a non-existing page', function () {
    $this->get('/non-existing-page')->assertNotFound();
});


// You can evaluate HTML for specific markup using a CSS selector to find your expected markup and then make assertions
// on the content within. For example, to check that there is a sidebar of recent news that contains five news articles
// you could do the following.
it('has a sidebar', function () {
    get('/')
        ->querySelector('.sidebar li')
        ->assertCount(5);
})->skip();

// Or, if you knew the exact text to expect you could make an expectation about the `textContent` of those nodes
it('expects known news titles', function () {
    $newsTitles = get('/')->querySelector('ul li')->text;

    expect($newsTitles)->toBe(["Article One", "Article Two", "Article N..."]);
})->skip();

// Factories allow you to generate content specific to your test cases so you can validate Twig logic. A common
// use for this is to create an entry and confirm it appears on the page(s) you expect it to appear on.
it('loads the news listing and displays the most recent news article', function () {
    $entry = Entry::factory()->create();

    // Check that the entry appears on the news listing page
    $this->get('/news')->assertSee($entry->title);

    // Check that the news detail page correctly loads the title in to the H1.
    $this->get($entry->uri)->querySelector('h1')->assertText($entry->title);

    // In reality, this isn't a very useful test case because the test matches your HTML 1:1. There's
    // no _logic_ being tested. You're simply testing that a developer wrote <h1>{{ entry.title }}</h1>
    // in to the appropriate Twig template.
    //
    // Instead, you usually want to test _logic_ from your template such as whether a list of related
    // news correctly filters out the news page you're currently looking at. That's not a native feature
    // of Craft and something that a developer wrote in to the template intentionally. That's a good bit
    // of logic to test against.
    expect($this->get($entry->uri))
        ->querySelector('.related li')
        ->text->not->toNotContain($entry->title);
})->skip();

// By using actingAs() the next requests emulate a logged-in user
// You can pass a user email, user handle or an User object
test('admin can see Craft version info in CP', function () {
    $this->actingAs('admin')
        ->get('/admin/settings')
        ->assertOk()
        ->querySelector("#app-info")->assertContainsString("Craft CMS 4");
});

// You can check for headers
it('promotes craft')
    ->get('/')
    ->assertHeader('x-powered-by', 'Craft CMS');
