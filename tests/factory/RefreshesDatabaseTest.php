<?php

it('refreshes the database for each test', function () {

    // 1 Initial state
    $countInitial = \craft\elements\Entry::find()->count();

    // 2 Create a channel with some Entries
   $channel = \markhuot\craftpest\factories\Section::factory()
        ->type('channel')
        ->create(['handle' => 'news']);
    \markhuot\craftpest\factories\Entry::factory()
        ->section($channel->handle)
        ->count(3)
        ->create();

    // Count after adding
    $countAfter = \craft\elements\Entry::find()->count();

    expect($countInitial)->toEqual(0);
    expect($countAfter)->toEqual(3);

});


it('rolls back even if a field factory triggered a transaction commit', function () {

    // Initial state
    $countInitial = \craft\elements\Entry::find()->count();

    // Try to break (commit) transaction by adding a field
    $plainTextField = \markhuot\craftpest\factories\Field::factory()
        ->type(\craft\fields\PlainText::class)
        ->group('Common')
        ->create();

    // Create a channel with 5 Entries
    $channel = \markhuot\craftpest\factories\Section::factory()
        ->type('channel')
        ->create(['handle' => 'news2']);

    $res = \markhuot\craftpest\factories\Entry::factory()
        ->section($channel->handle)
        ->count(5)
        ->create();

    // Count after adding
    $countAfter = \craft\elements\Entry::find()->count();

    expect($countInitial)->toEqual(0);
    expect($countAfter)->toEqual(5);

});

