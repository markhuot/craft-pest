<?php

use craft\fields\PlainText;
use markhuot\craftpest\factories\Field;
use markhuot\craftpest\factories\User;
use markhuot\craftpest\factories\Section;
use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\exceptions\ModelStoreException;

it('fails on required fields', function () {
    $field = Field::factory()->type(PlainText::class)->required(true)->create();
    $user = User::factory()->create();
    $section = Section::factory()->fields($field)->create();

    $this->expectException(ModelStoreException::class);

    $entry = Entry::factory()
        ->section($section)
        ->scenario(\craft\elements\Entry::SCENARIO_LIVE)
        ->author($user)
        ->create();

    $entry->assertInvalid([$field->handle]);
});
