<?php

use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\Section;
use markhuot\craftpest\factories\User;
    
it ('cookie `foo` survives request cycle', function () {
    $user = User::factory()->admin(true)->create();
    $this->actingAs($user);

    // Initial request
    $response0 = $this->action('pest-module-test/test/cookie-increment')->assertOk();
    expect($response0->content)->toBeJson();
    expect(json_decode($response0->content, true))->toBe(['counter' => 0]);

    // Response contains cookie
    expect($response0->getCookies()->get('foo')->expire)->toBe(100);

    // Next request
    $response1 = $this->action('pest-module-test/test/cookie-increment')->assertOk();
    expect($response1->content)->toBeJson();
    expect(json_decode($response1->content, true))->toBe(['counter' => 1]);

});


it ('session `foo` survives request cycle', function () {
    $user = User::factory()->admin(true)->create();
    $this->actingAs($user);

    $response1 = $this->action('pest-module-test/test/session')->assertOk();
    expect(json_decode($response1->content, true))->toBe([1]);

    $response2 = $this->action('pest-module-test/test/session')->assertOk();
    expect(json_decode($response2->content, true))->toBe([2]);

    $response3 = $this->action('pest-module-test/test/session')->assertOk();
    expect(json_decode($response3->content, true))->toBe([3]);
});

it ('session `foo` survives request <> test cycle', function () {
    $user = User::factory()->admin(true)->create();
    $this->actingAs($user);

    $response = $this->action('pest-module-test/test/session')->assertOk();
    expect(json_decode($response->content, true))->toBe([4]);
});
