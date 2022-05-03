<?php

it('can bootstrap craft', function () {
    expect(\Craft::$app)->toBeTruthy();
});
