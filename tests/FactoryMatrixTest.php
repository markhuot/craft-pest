<?php

use markhuot\craftpest\factories\Block;

it('can fill matrix fields', function () {
    $entry = \markhuot\craftpest\factories\Entry::factory()
        ->section('posts')
        ->matrixField(
            Block::factory()->type('blockTypeOne')->fieldOne('foo'),
            Block::factory()->type('blockTypeOne')->fieldOne('bar'),
        )
        ->create();

    expect($entry->matrixField->all())->toHaveCount(2);
});

it('can fill matrix fields with multiple blocks', function () {
    $entry = \markhuot\craftpest\factories\Entry::factory()
        ->section('posts')
        ->matrixField(
            Block::factory()->type('blockTypeOne')->count(5),
        )
        ->create();

    expect($entry->matrixField->all())->toHaveCount(5);
});

it('can create matrix fields', function () {
    $plainText = \markhuot\craftpest\factories\Field::factory()
        ->type(\craft\fields\PlainText::class);

    $matrix = \markhuot\craftpest\factories\MatrixField::factory()
        ->blockTypes(
            $blockType = \markhuot\craftpest\factories\BlockType::factory()
                ->fields([$plainText])
        )
        ->create();

    $section = \markhuot\craftpest\factories\Section::factory()
        ->fields([$matrix])
        ->create();

    $entry = \markhuot\craftpest\factories\Entry::factory()
        ->section($section->handle)
        ->{$matrix->handle}(
            Block::factory()->type($blockType->getMadeModels()->first()->handle)
        )
        ->create();

    expect((int)$entry->{$matrix->handle}->count())->toBe(1);
});
