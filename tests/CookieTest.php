<?php

use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\Section;
use markhuot\craftpest\factories\User;
    
it ('increases a counter and keeps the value in a cookie', function () {
    $user = User::factory()->admin(true)->create();
    $this->actingAs($user);

    // Initial request
    $response0 = $this->action('pest-module-test/test/cookie-increment')->assertOk();
    expect($response0->content)->toBeJson();
    expect(json_decode($response0->content, true))->toBe(['counter' => 0]);

    // Next request
    $response1 = $this->action('pest-module-test/test/cookie-increment')->assertOk();
    expect($response1->content)->toBeJson();
    expect(json_decode($response1->content, true))->toBe(['counter' => 1]);


});
