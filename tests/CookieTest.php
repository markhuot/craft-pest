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
