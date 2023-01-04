<?php

use markhuot\craftpest\factories\Field;
use markhuot\craftpest\factories\User;
use markhuot\craftpest\factories\Section;
use markhuot\craftpest\factories\Entry;

it('fails on required fields', function () {
    $field = Field::factory()->type(\craft\fields\PlainText::class)->required(true)->create();
    $user = User::factory()->create();
    $section = Section::factory()->fields($field)->create();
    $entry = Entry::factory()->muteValidationErrors()->section($section)->scenario(\craft\elements\Entry::SCENARIO_LIVE)->author($user)->create();

    $entry->assertInvalid([$field->handle]);
});
