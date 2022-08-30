<?php

use function markhuot\craftpest\helpers\http\get;

beforeEach(function () {
    \Craft::setAlias('@templates', '/Users/markhuot/Sites/craft-pest-ostark/tests/templates');
});

it('covers twig', function () {
   $response = get('/coverage-example');

   expect($response)->toBeTruthy();
});
