<?php

it('logs in users by email address', function () {
    $user = \markhuot\craftpest\factories\User::factory()->create();

    $this->actingAs($user->email);

    expect(\Craft::$app->user->identity->email)->toBe($user->email);
});

it('logs in user objects', function () {
    $user = \markhuot\craftpest\factories\User::factory()->create();

    $this->actingAs($user);

    expect(\Craft::$app->user->identity->email)->toBe($user->email);
});

it('throws on missing users', function () {
    $this->expectException(\Exception::class);
    $this->actingAs('foobar');
});
