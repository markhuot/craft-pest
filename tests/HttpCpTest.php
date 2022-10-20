<?php

use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\Section;
use markhuot\craftpest\factories\User;

it('determines cp URLs with index.php', function() {
    \Craft::$app->config->general->omitScriptNameInUrls = false;
    $section = Section::factory()->create();
    $entry = Entry::factory()->section($section)->create();
    $user = User::factory()->admin(true)->create();

    expect($entry->cpEditUrl)->toContain('index.php?p=');
    $this->actingAs($user)
        ->get($entry->cpEditUrl)
        ->assertOk();
});

it('determines cp URLs without index.php', function() {
    \Craft::$app->config->general->omitScriptNameInUrls = true;
    $section = Section::factory()->create();
    $entry = Entry::factory()->section($section)->create();
    $user = User::factory()->admin(true)->create();

    expect($entry->cpEditUrl)->not->toContain('index.php?p=');
    $this->actingAs($user)
        ->get($entry->cpEditUrl)
        ->assertOk();
});

it('supports non-standard path param', function () {
    \Craft::$app->config->general->omitScriptNameInUrls = false;
    \Craft::$app->config->general->pathParam = 'foo';
    $section = Section::factory()->create();
    $entry = Entry::factory()->section($section)->create();
    $user = User::factory()->admin(true)->create();

    expect($entry->cpEditUrl)->toContain('index.php?foo=');
    $this->actingAs($user)
        ->get($entry->cpEditUrl)
        ->assertOk();
});

it('supports non-standard cpTrigger', function () {
    \Craft::$app->config->general->omitScriptNameInUrls = true;
    \Craft::$app->config->general->cpTrigger = 'foobar';
    $section = Section::factory()->create();
    $entry = Entry::factory()->section($section)->create();
    $user = User::factory()->admin(true)->create();

    expect($entry->cpEditUrl)->toContain('foobar');
    $this->actingAs($user)
        ->get($entry->cpEditUrl)
        ->assertOk();
});

it('sends action requests', function () {
    $this->action('dashboard/widget/create')
        ->assertOk();
})->only();