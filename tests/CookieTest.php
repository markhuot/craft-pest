<?php

use markhuot\craftpest\factories\User;

it('receives cookies')
    ->get('response-test')
    ->assertCookie('cookieName');

it('sends cookies', function () {
    $content = $this->http('get', 'responses/cookies')
        ->addCookie('theName', 'theValue')
        ->send()
        ->content;
        
    expect(trim($content))->toBe(json_encode(['theName' => 'theValue']));
});

it('retains cookies', function () {
    $this->get('response-test')
        ->assertOk()
        ->assertCookie('cookieName');

    $this->get('responses/cookies')
        ->assertOk()
        ->expect()
        ->jsonContent->toBe(['cookieName' => 'cookieValue']);
    
    $this->get('responses/cookies')
        ->assertOk()
        ->expect()
        ->jsonContent->toBe(['cookieName' => 'cookieValue']);
});

it('doesn\'t have any cookies from previous tests', function () {
    $response = $this->get('responses/cookies');
    expect($response->jsonContent)->toHaveCount(0);
});
    
test('cookie `foo` survives request cycle', function () {
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

})->skip(true, 'workaround is not implemented');


test('session `foo` survives request cycle', function () {
    $user = User::factory()->admin(true)->create();
    $this->actingAs($user);

    $response1 = $this->action('pest-module-test/test/session')->assertOk();
    expect(json_decode($response1->content, true))->toBe([1]);

    $response2 = $this->action('pest-module-test/test/session')->assertOk();
    expect(json_decode($response2->content, true))->toBe([2]);

    $response3 = $this->action('pest-module-test/test/session')->assertOk();
    expect(json_decode($response3->content, true))->toBe([3]);
});

test('session `foo` survives request <> test cycle', function () {
    $user = User::factory()->admin(true)->create();
    $this->actingAs($user);

    $response = $this->action('pest-module-test/test/session')->assertOk();
    expect(json_decode($response->content, true))->toBe([4]);
});
