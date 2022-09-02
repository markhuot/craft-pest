<?php

it('can create matrix fields', function () {
    $textField = \markhuot\craftpest\factories\Field::factory()
        ->name('Test Text Field')
        ->type(\craft\fields\PlainText::class);

    $blockType = \markhuot\craftpest\factories\BlockType::factory()
        ->name('Test Block Type')
        ->addField($textField);

    $matrixField = \markhuot\craftpest\factories\MatrixField::factory()
        ->addBlockType($blockType)
        ->create();

    $section = \markhuot\craftpest\factories\Section::factory()
        ->addField($matrixField)
        ->hasUrls(false)
        ->create();

    $entry = \markhuot\craftpest\factories\Entry::factory()
        ->section($section->handle)
        ->{$matrixField->handle}([
            'NEW1' => [
                'type' => 'testBlockType',
                'enabled' => 1,
                'fields' => [
                    'testTextField' => 'foo',
                ],
            ],
        ])
        ->create();

    expect($entry->errors)->toBeEmpty();
    //var_dump($entry->{$matrixField->handle}->one()->{$textFieldHandle});
});
