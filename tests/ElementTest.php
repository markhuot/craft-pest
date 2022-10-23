<?php

use markhuot\craftpest\factories\Entry;

it('asserts valid')
    ->factory(Entry::class)
    ->create()
    ->assertValid();

it('asserts invalid')
    ->factory(Entry::class)
    ->silenceErrors()
    ->create(['title' => null])
    ->assertInvalid();
