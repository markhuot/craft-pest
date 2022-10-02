<?php

it('asserts cookie presence')
  ->get('/response-test')
  ->assertOk()
  ->assertCookie('foo');

it('asserts cookie presence')
  ->get('/response-test')
  ->assertOk()
  ->assertCookie('foo', 'bar');
