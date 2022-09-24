<?php

use craft\fields\Entries;
use craft\fields\Matrix;
use craft\fields\PlainText;
use function markhuot\craftpest\helpers\http\get;

it('can create singles', function () {
    $section = \markhuot\craftpest\factories\Section::factory()
        ->type('single')
        ->create();

    expect(Craft::$app->sections->getSectionByHandle($section->handle)->type)->toBe('single');
});

it('can create channels', function () {
    $section = \markhuot\craftpest\factories\Section::factory()
        ->type('channel')
        ->create();

    expect(Craft::$app->sections->getSectionByHandle($section->handle)->type)->toBe('channel');
});

it('can create structures', function () {
    $section = \markhuot\craftpest\factories\Section::factory()
        ->type('structure')
        ->create();

    expect(Craft::$app->sections->getSectionByHandle($section->handle)->type)->toBe('structure');
});

it('can set hasUrls of the section', function () {
    $section = \markhuot\craftpest\factories\Section::factory()
        ->hasUrls(false)
        ->create();

    $siteId = \Craft::$app->sites->getCurrentSite()->id;
    expect(Craft::$app->sections->getSectionByHandle($section->handle)->siteSettings[$siteId]->hasUrls)->toBe(false);
});

it('can set uriFormat of the section', function () {
    $section = \markhuot\craftpest\factories\Section::factory()
        ->uriFormat('{sluggy}')
        ->create();

    $siteId = \Craft::$app->sites->getCurrentSite()->id;
    expect(Craft::$app->sections->getSectionByHandle($section->handle)->siteSettings[$siteId]->uriFormat)->toBe('{sluggy}');
});

it('can set enabledByDefault of the section', function () {
    $section = \markhuot\craftpest\factories\Section::factory()
        ->enabledByDefault(false)
        ->create();

    $siteId = \Craft::$app->sites->getCurrentSite()->id;
    expect(Craft::$app->sections->getSectionByHandle($section->handle)->siteSettings[$siteId]->enabledByDefault)->toBe(false);
});

it('can set template of the section', function () {
    $section = \markhuot\craftpest\factories\Section::factory()
        ->template('_foo/{handle}/bar')
        ->create();

    $siteId = \Craft::$app->sites->getCurrentSite()->id;
    expect(Craft::$app->sections->getSectionByHandle($section->handle)->siteSettings[$siteId]->template)->toBe(implode('/', ['_foo', $section->handle, 'bar']));
});

it('can fill an entries field', function () {
    $entry = \markhuot\craftpest\factories\Entry::factory()
        ->section('posts')
        ->entriesField(
            \markhuot\craftpest\factories\Entry::factory()->section('posts')
        )
        ->create();

    expect($entry->entriesField->all())->toHaveCount(1);
});

it('can place fields in groups', function () {
    $field = \markhuot\craftpest\factories\Field::factory()
        ->type(Entries::class)
        ->group('Common')
        ->create();

    expect($field->getGroup()->name)->toBe('Common');
})->skip();

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

    // Set element references via an array passed to make/create
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
})->skip();

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

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toEqual(2);
})->with('entries field')->skip();

it('automatically resolves factories with ->count()', function ($props) {
    [$factory, $section, $field] = $props;

    $entry = $factory->{$field->handle}(
        \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->count(5),
    )->create();

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toEqual(5);
})->with('entries field')->skip();

it('automatically resolves factories via ->create() definition', function ($props) {
    [$factory, $section, $field] = $props;

    $entry = $factory->create([
        $field->handle => \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->count(5),
    ]);

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toEqual(5);
})->with('entries field')->skip();

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
})->with('entries field')->skip();

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
        ->name('whoops1')
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
})->skip();
