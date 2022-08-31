<?php

it('logs in users', function () {
    $this->actingAs('michael@bluth.com');

    expect(\Craft::$app->user->identity->email)->toBe('michael@bluth.com');
});

it('throws on missing users', function () {
    $this->expectException(\Exception::class);
    $this->actingAs('foobar');
});
