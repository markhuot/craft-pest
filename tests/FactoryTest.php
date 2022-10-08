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

it('can create entries with section id, handle, and object', function () {
    $section = \markhuot\craftpest\factories\Section::factory()->create();

    $setById = \markhuot\craftpest\factories\Entry::factory()->section($section->id)->create();
    expect($setById->errors)->toBeEmpty();
    
    $setByHandle = \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->create();
    expect($setByHandle->errors)->toBeEmpty();
    
    $setByObject = \markhuot\craftpest\factories\Entry::factory()->section($section)->create();
    expect($setByObject->errors)->toBeEmpty();
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

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toEqual(2);
})->with('entries field');

it('automatically resolves factories with ->count()', function ($props) {
    [$factory, $section, $field] = $props;

    $entry = $factory->{$field->handle}(
        \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->count(5),
    )->create();

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toEqual(5);
})->with('entries field');

it('automatically resolves factories via ->create() definition', function ($props) {
    [$factory, $section, $field] = $props;

    $entry = $factory->create([
        $field->handle => \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->count(5),
    ]);

    expect(\craft\elements\Entry::find()->id($entry->id)->one()->{$field->handle}->count())->toEqual(5);
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

it('allows you to use ->set()` on a factory', function () {
    $section = \markhuot\craftpest\factories\Section::factory()->create();
    $entry = \markhuot\craftpest\factories\Entry::factory()->section($section->handle)->set('title', 'foo')->create();

    expect($entry->title)->toBe('foo');
});
