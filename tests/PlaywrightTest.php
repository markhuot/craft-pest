<?php

it('executes commands')
    ->exec(['echo', 'hello world'])
    ->assertOk()
    ->assertStdout('hello world')
    ->assertSee('hello world')
    ->assertDontSee('foo bar');

it('runs playwright via any available port')
    ->playwright()
    ->assertOk();

it('runs playwright via manual port')
    ->skip()
    ->serve(9001)
    ->exec(['npx', 'playwright', 'test', '--reporter=dot'], null, ['PLAYWRIGHT_BASE_URL' => 'http://127.0.0.1:9001'])
    ->assertOk();
