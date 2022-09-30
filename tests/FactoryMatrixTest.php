<?php

use markhuot\craftpest\factories\Entry as EntryFactory;
use markhuot\craftpest\factories\MatrixField as MatrixFieldFactory;
use markhuot\craftpest\factories\Block as BlockFactory;
use markhuot\craftpest\factories\Field as FieldFactory;
use craft\fields\PlainText as PlainTextField;
use markhuot\craftpest\factories\BlockType as BlockTypeFactory;

it('can fill matrix fields', function () {
    $entry = EntryFactory::factory()
        ->section('posts')
        ->matrixField(
            BlockFactory::factory()->type('blockTypeOne')->fieldOne('foo'),
            BlockFactory::factory()->type('blockTypeOne')->fieldOne('bar'),
        )
        ->create();

    expect($entry->matrixField->all())->toHaveCount(2);
});

it('can fill matrix fields with multiple blocks', function () {
    $entry = EntryFactory::factory()
        ->section('posts')
        ->matrixField(
            BlockFactory::factory()->type('blockTypeOne')->count(5),
        )
        ->create();

    expect($entry->matrixField->all())->toHaveCount(5);
});

it('can create matrix fields', function () {
    $plainText = FieldFactory::factory()
        ->type(PlainTextField::class);

    $matrix = MatrixFieldFactory::factory()
        ->blockTypes($blockType = BlockTypeFactory::factory()->fields([$plainText]))
        ->create();

    $section = \markhuot\craftpest\factories\Section::factory()
        ->fields([$matrix])
        ->create();

    $entry = EntryFactory::factory()
        ->section($section->handle)
        ->{$matrix->handle}(
            BlockFactory::factory()
                ->type($blockType->getMadeModels()->first()->handle)
                ->count(5)
        )
        ->create();

    expect((int)$entry->{$matrix->handle}->count())->toBe(5);
})->only();
