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
// use for this is to create an entry and confirm it appears on the page you expect it to appear on.
it('loads the news listing and displays the most recent news article', function () {
    $entry = Entry::factory()->create();
    $this->get('/news')->assertSee($entry->title);
})->skip();
