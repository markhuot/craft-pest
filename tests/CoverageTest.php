<?php

use function markhuot\craftpest\helpers\http\get;

beforeEach(function () {
    \Craft::setAlias('@templates', __DIR__ . '/templates');
});

get('/')->assertOk();
get('/?foo=1')->assertOk();

it('covers loops', function () {
    \markhuot\craftpest\factories\Section::factory()
        ->name('News')
        ->handle('news')
        ->create();

    \markhuot\craftpest\factories\Entry::factory()
        ->section('news')
        ->create();

    get('/loop-test')->assertOk();
});