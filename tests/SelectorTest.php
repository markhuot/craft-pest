<?php

use function markhuot\craftpest\helpers\http\get;

it('finds selectors', function () {
    get('/selectors')
        ->expect()
        ->querySelector('h1')
        ->text->toBe('heading text');
});

it('gets inner html by class name', function () {
    get('/selectors')
        ->expect()
        ->querySelector('.class-name')
        ->innerHTML->toBe('inner html');
});
