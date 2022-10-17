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