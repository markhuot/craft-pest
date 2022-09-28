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


it('rolls back', function () {

    // 1 Initial state
    $countInitial = \craft\elements\Entry::find()->count();

    // 2 Create a channel with some Entries
    $channel = \markhuot\craftpest\factories\Section::factory()
        ->type('channel')
        ->create();

    \markhuot\craftpest\factories\Entry::factory()
        ->section($channel->handle)
        ->count(3)
        ->create();

    $this->rollbackNow();

    // Count after adding
    $countAfter = \craft\elements\Entry::find()->count();

    expect($countInitial)->toEqual(0);
    expect($countAfter)->toEqual(0);

})->skip();


it('rolls back even if a field factory triggered a transaction commit', function () {

    // 1 Initial state
    $countInitial = \craft\elements\Entry::find()->count();

    // 2 Create a channel with some Entries
    $channel = \markhuot\craftpest\factories\Section::factory()
        ->type('channel')
        ->create(['handle' => 'news']);
    \markhuot\craftpest\factories\Entry::factory()
        ->section($channel->handle)
        ->count(5)
        ->create();

    $plainTextField = \markhuot\craftpest\factories\Field::factory()
        ->type(\craft\fields\PlainText::class)
        ->group('Common')
        ->create();

    // Count after adding
    $countAfter = \craft\elements\Entry::find()->count();

    expect($countInitial)->toEqual(0);
    expect($countAfter)->toEqual(0);

});

