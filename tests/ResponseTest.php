<?php

it('asserts cookie presence')
  ->get('/response-test')
  ->assertCookie('foo');

it('asserts cookie presence')
  ->get('/response-test')
  ->assertCookie('foo', 'bar);
