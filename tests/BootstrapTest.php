<?php

use craft\fields\Entries;
use craft\fields\Matrix;
use craft\fields\PlainText;
use function markhuot\craftpest\helpers\http\get;

it('can bootstrap craft', function () {
    expect(\Craft::$app)->toBeTruthy();
});

it('can create sections', function () {
    $section = \markhuot\craftpest\factories\Section::factory()
        ->create();

    $createdEntries = \markhuot\craftpest\factories\Entry::factory()
        ->section($section->handle)
        ->count(5)
        ->create();

    $foundEntries = collect(\craft\elements\Entry::find()
        ->section($section->handle)
        ->all());

    expect($foundEntries->count())->toBe($createdEntries->count());
    expect($foundEntries->pluck('title')->toArray())->toEqualCanonicalizing($createdEntries->pluck('title')->toArray());
});

it('can create fields', function () {
    $field = \markhuot\craftpest\factories\Field::factory()
        ->type(Entries::class)
        ->create();

    $section = \markhuot\craftpest\factories\Section::factory()
        ->fields([$field])
        ->create();

    // Create a few child entries
    [$first, $second] = \markhuot\craftpest\factories\Entry::factory()
        ->section($section->handle)
        ->count(2)
        ->create()
        ->toArray();

    // Set element referenecs via an array passed to make/create
    $parents[] = \markhuot\craftpest\factories\Entry::factory()
        ->section($section->handle)
        ->create([
            $field->handle => [$first->id, $second->id],
        ]);

    // Set element reference via an array passed to a magic method
    $parents[] = \markhuot\craftpest\factories\Entry::factory()
        ->section($section->handle)
        ->{$field->handle}([$first->id, $second->id])
        ->create();

    // Set element references via an ...$args array passed to a magic method
    $parents[] = \markhuot\craftpest\factories\Entry::factory()
        ->section($section->handle)
        ->{$field->handle}($first->id, $second->id)
        ->create();

    foreach ($parents as $parent) {
        expect(
            \craft\elements\Entry::find()
                ->id($parent->id)
                ->one()
                ->{$field->handle}
                ->ids()
            )
            ->toEqualCanonicalizing([$first->id, $second->id]);
    }
});

dataset('entries field', function () {
    yield function () {
        $field = \markhuot\craftpest\factories\Field::factory()
        ->type(Entries::class)
        ->create();

        $section = \markhuot\craftpest\factories\Section::factory()
            ->fields([$field])
            ->create();

        $factory = \markhuot\craftpest\factories\Entry::factory()
            ->section($section->handle);

        return [$factory, $section, $field];
    };
});

it('automatically resolves factories via method', function ($props) {
    [$factory, $section, $field] = $props;

    $entry = $factory->{$field->handle}(
        \markhuot\craftpest\factories\Entry::factory()->section($section->handle),
        \markhuot\craftpest\factories\Entry::factory()->section($section->handle),
    )->create();

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toBe(2);
})->with('entries field');

it('automatically resolves factories with ->count()', function ($props) {
    [$factory, $section, $field] = $props;

    $entry = $factory->{$field->handle}(
        \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->count(5),
    )->create();

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toBe(5);
})->with('entries field');

it('automatically resolves factories via ->create() definition', function ($props) {
    [$factory, $section, $field] = $props;

    $entry = $factory->create([
        $field->handle => \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->count(5),
    ]);

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toBe(5);
})->with('entries field');

it('takes an array of entries', function ($props) {
    [$factory, $section, $field] = $props;

    $children = \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->count(3)->create();

    $entry = $factory->create([
        $field->handle => [
            $children[0],
            $children[1],
            $children[2],
        ]
    ]);

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->ids())->toEqualCanonicalizing($children->pluck('id')->toArray());
})->with('entries field');

it('can create matrix blocks', function () {
    $textField = \markhuot\craftpest\factories\Field::factory()
        ->type(PlainText::class);

    $matrixTextField = \markhuot\craftpest\factories\Field::factory()
        ->type(PlainText::class);

    $matrixField = \markhuot\craftpest\factories\MatrixField::factory()
        ->blockTypes(
            \markhuot\craftpest\factories\BlockType::factory()
                ->fields([$matrixTextField])
        );

    $section = \markhuot\craftpest\factories\Section::factory()
        ->fields([$matrixField, $textField])
        ->create();

    $matrixFieldObj = $section->entryTypes[0]->fieldLayout->getFields()[0];
    $textFieldObj = $section->entryTypes[0]->fieldLayout->getFields()[1];
    $nestedTextFieldObj = $matrixFieldObj->blockTypes[0]->fieldLayout->getFields()[0];

    $entry = \markhuot\craftpest\factories\Entry::factory()
        ->section($section->handle)
        ->{$textFieldObj->handle}('bar')
        ->{$matrixFieldObj->handle}([
            'NEW1' => [
                'type' => $matrixFieldObj->blockTypes[0]->handle,
                'enabled' => 1,
                'fields' => [
                    $nestedTextFieldObj->handle => 'foo',
                ],
            ],
            // \markhuot\craftpest\factories\MatrixBlock::factory()
            //     ->type('foo')
            //     ->customField('bar')
        ])
        ->create();


    expect($entry->id)->toBeTruthy();
    expect($entry->{$textFieldObj->handle})->toBe('bar');
    expect($entry->{$matrixFieldObj->handle}->all())->toHaveCount(1);
    expect($entry->{$matrixFieldObj->handle}->one()->{$nestedTextFieldObj->handle})->toBe('foo');
    //var_dump($entry->{$matrixField->handle}->one()->{$textField->handle});
    //var_dump($textField->handle, $matrixField->blockTypes[0]->fieldLayout->getFieldByHandle($textField->handle));
});
