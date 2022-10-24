<?php

use markhuot\craftpest\factories\Entry;

it('asserts valid')
    ->factory(Entry::class)
    ->create()
    ->assertValid();

it('asserts invalid')
    ->factory(Entry::class)
    ->muteValidationErrors()
    ->create(['title' => null])
    ->assertInvalid();
