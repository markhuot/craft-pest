<?php

it('asserts cookie presence')
  ->get('/response-test')
  ->assertOk()
  ->assertCookie('cookieName');

it('asserts cookie value')
  ->get('/response-test')
  ->assertOk()
  ->assertCookie('cookieName', 'cookieValue');

it('asserts cookie valid')
    ->get('/response-test')
    ->assertOk()
    ->assertCookieNotExpired('cookieName');

it('asserts expired cookies')
    ->get('/response-test')
    ->assertOk()
    ->assertCookieExpired('expiredCookieName');

it('asserts missing cookies')
    ->get('/response-test')
    ->assertOk()
    ->assertCookieMissing('foo');

it('asserts 201 Created status code')
    ->get('/responses/201')
    ->assertCreated();

it('asserts not seeing text')
    ->get('/response-test')
    ->assertDontSee('fuzz');

it('asserts a download')
    ->get('/responses/download')
    ->assertDownload('file.jpg');

it('asserts json')
    ->get('/responses/json')
    ->assertExactJson(['foo' => 'bar', 'baz' => ['qux']]);

it('asserts 403 Forbidden status code')
    ->get('/responses/403')
    ->assertForbidden();

it('asserts header presence')
    ->get('/responses/header')
    ->assertHeader('x-foo');

it('asserts header value')
    ->get('/responses/header')
    ->assertHeader('x-foo', 'bar');

it('asserts missing header')
    ->get('/responses/header')
    ->assertHeaderMissing('x-qux');

it('asserts flash data')
    ->get('/responses/flash')
    ->assertFlash('You\'re not allowed to go there.');

it('asserts flash data by key')
    ->get('/responses/flash')
    ->assertFlash('You\'re not allowed to go there.', 'error');

it('redirects match path')
    ->get('/responses/302')
    ->assertRedirectTo('/');

it('redirects match hostname')
    ->get('/responses/302')
    ->assertRedirectTo('http://localhost:8080/');

it('redirects match offsite')
    ->get('/responses/302-offsite')
    ->assertRedirectTo('https://www.example.com/');

it('follows redirects')
    ->get('/responses/302')
    ->followRedirect()
    ->assertOk()
    ->assertSee('Hello World!');
