<?php

use markhuot\craftpest\factories\Section;
use markhuot\craftpest\factories\Entry;

it('counts entries', function() {
    Entry::factory()
        ->section($section = Section::factory()->create())
        ->count(10)
        ->create();

    \craft\elements\Entry::find()->section($section)->assertCount(10);
});
