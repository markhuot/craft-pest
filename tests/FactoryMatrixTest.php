<?php

use markhuot\craftpest\factories\Block;

it('can create matrix fields', function () {
    $entry = \markhuot\craftpest\factories\Entry::factory()
        ->section('posts')
        ->matrixField(
            Block::factory()->type('blockTypeOne')->fieldOne('foo'),
            Block::factory()->type('blockTypeOne')->fieldOne('bar'),
        )
        ->create();

    expect($entry->matrixField->all())->toHaveCount(2);
});
