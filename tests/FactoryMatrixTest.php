<?php

use markhuot\craftpest\factories\Entry as EntryFactory;
use markhuot\craftpest\factories\MatrixField as MatrixFieldFactory;
use markhuot\craftpest\factories\Block as BlockFactory;
use markhuot\craftpest\factories\Field as FieldFactory;
use craft\fields\PlainText as PlainTextField;
use markhuot\craftpest\factories\BlockType as BlockTypeFactory;
use markhuot\craftpest\factories\Section as SectionFactory;

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
    $plainTextOne = FieldFactory::factory()
        ->type(PlainTextField::class);

    $plainTextTwo = FieldFactory::factory()
        ->type(PlainTextField::class);

    $blockType = BlockTypeFactory::factory()
        ->fields($plainTextOne, $plainTextTwo);

    $matrix = MatrixFieldFactory::factory()
        ->blockTypes($blockType)
        ->create();

    $section = SectionFactory::factory()
        ->fields($matrix)
        ->create();

    $blockTypeHandle = $blockType->getMadeModels()->first()->handle;
    $plainTextOneHandle = $plainTextOne->getMadeModels()->first()->handle;
    $plainTextTwoHandle = $plainTextTwo->getMadeModels()->first()->handle;

    $entry = EntryFactory::factory()
        ->section($section->handle)
        ->{$matrix->handle}(
            BlockFactory::factory()
                ->type($blockTypeHandle)
                ->{$plainTextOneHandle}('foo')
                ->{$plainTextTwoHandle}('bar')
                ->count(5)
        )
        ->create();

    $blocks = $entry->{$matrix->handle}->all();
    expect($blocks)->toHaveCount(5);

    $firstBlock = $blocks[0];
    expect($firstBlock->{$plainTextOneHandle})->toBe('foo');
    expect($firstBlock->{$plainTextTwoHandle})->toBe('bar');
});

it('can fill matrix blocks with a shorthand', function () {
    $plainTextOne = FieldFactory::factory()->type(PlainTextField::class);
    $plainTextTwo = FieldFactory::factory()->type(PlainTextField::class);
    $blockType = BlockTypeFactory::factory()->fields($plainTextOne, $plainTextTwo);
    $matrix = MatrixFieldFactory::factory()->blockTypes($blockType)->create();
    $section = SectionFactory::factory()->fields($matrix)->create();

    $blockTypeHandle = $blockType->getMadeModels()->first()->handle;
    $plainTextOneHandle = $plainTextOne->getMadeModels()->first()->handle;
    $plainTextTwoHandle = $plainTextTwo->getMadeModels()->first()->handle;

    $entry = EntryFactory::factory()
        ->section($section)
        ->addBlockTo($matrix, [
            $plainTextOneHandle => 'foo',
            $plainTextTwoHandle => 'bar',
        ])
        ->create();

    $block = $entry->{$matrix->handle}->all()[0];
    expect($block->{$plainTextOneHandle})->toBe('foo');
    expect($block->{$plainTextTwoHandle})->toBe('bar');
});

it('can fill matrix blocks with a magic shorthand', function () {
    $plainTextOne = FieldFactory::factory()->type(PlainTextField::class)->name('Plain Text One');
    $plainTextTwo = FieldFactory::factory()->type(PlainTextField::class)->name('Plain Text Two');
    $blockType = BlockTypeFactory::factory()->fields($plainTextOne, $plainTextTwo);
    $matrix = MatrixFieldFactory::factory()->blockTypes($blockType)->create();
    $section = SectionFactory::factory()->fields($matrix)->create();

    $blockTypeHandle = $blockType->getMadeModels()->first()->handle;
    $plainTextOneHandle = $plainTextOne->getMadeModels()->first()->handle;
    $plainTextTwoHandle = $plainTextTwo->getMadeModels()->first()->handle;
    $matrixBlockMethod = 'add' . ucfirst($blockTypeHandle) . 'To' . ucfirst($matrix->handle);
    $matrixFieldMethod = 'addBlockTo' . ucfirst($matrix->handle);

    $entry = EntryFactory::factory()
        ->section($section)
        ->$matrixBlockMethod(
            fieldOne: 'foo',
            fieldTwo: 'bar',
        )
        ->$matrixFieldMethod(
            plainTextOne: 'foo',
            plainTextTwo: 'bar',
        )
        ->create();

    $block = $entry->{$matrix->handle}->all()[0];
    expect($block->{$plainTextOneHandle})->toBe('foo');
    expect($block->{$plainTextTwoHandle})->toBe('bar');
});
