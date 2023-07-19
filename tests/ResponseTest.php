<?php

it('asserts cache tag')
    ->get('/responses/cache-tags')
    ->assertOk()
    ->assertCacheTag('foo', 'baz');


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

it('asserts redirect')
    ->get('/responses/302')
    ->assertRedirect();

it('asserts redirect to')
    ->get('/responses/302')
    ->assertRedirectTo('/');

it('asserts redirect matching hostname')
    ->get('/responses/302')
    ->assertRedirectTo('http://localhost:8080/');

it('asserts redirect matching offsite hostname')
    ->get('/responses/302-offsite')
    ->assertRedirectTo('https://www.example.com/');

it('follows a redirect')
    ->get('/responses/302')
    ->followRedirect()
    ->assertOk()
    ->assertSee('Hello World!');

it('follows multiple redirects')
    ->get('/responses/302-nested')
    ->followRedirect()
    ->followRedirect()
    ->assertOk()
    ->assertSee('Hello World!');

it('follows multiple redirects in one call')
    ->get('/responses/302-nested')
    ->followRedirects()
    ->assertOk()
    ->assertSee('Hello World!');

it('asserts no content')
    ->get('/responses/204')
    ->assertNoContent();

it('asserts not found')
    ->get('/responses/404')
    ->assertNotFound();

it('asserts seeing a string')
    ->get('/responses/text')
    ->assertSee('five');

it('asserts seeing strings in order')
    ->get('/responses/text')
    ->assertSeeInOrder(['five', 'six']);

it('asserts seeing a string without tags')
    ->get('/responses/text')
    ->assertSeeText('five.5');

it('asserts seeing strings without tags in order')
    ->get('/responses/text')
    ->assertSeeTextInOrder(['five.5', 'six.6']);

it('asserts status')
    ->get('/responses/404')
    ->assertStatus(404);

it('asserts titles')
    ->get('/responses/title')
    ->assertOk()
    ->assertTitle('The Title');

it('asserts unauthorized')
    ->get('/responses/401')
    ->assertUnauthorized();

it('asserts location')
    ->get('/responses/302')
    ->assertLocation('/');

it('asserts location path')
    ->get('/responses/302-query-string')
    ->assertLocation('/', ['path']);

it('asserts location path by shorthand')
    ->get('/responses/302-query-string')
    ->assertLocationPath('/');

it('returns HTML for exceptions')
    ->get('/responses/500')
    ->assertStatus(500);

it('can skip html errors and bubble the actual exception')
    ->withoutExceptionHandling()
    ->expectException(\yii\web\ServerErrorHttpException::class)
    ->get('/responses/500');

