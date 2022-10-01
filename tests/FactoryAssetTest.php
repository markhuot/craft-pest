<?php

use markhuot\craftpest\factories\Volume;
use markhuot\craftpest\factories\Asset;
use markhuot\craftpest\factories\VolumeFolder;

it('can create volumes and assets', function () {
    $volume = Volume::factory()->create();
    $asset = Asset::factory()->volume($volume->handle)->create();

    $assets = \craft\elements\Asset::find()
        ->volume($volume)
        ->filename($asset->filename)
        ->all();

    expect($assets)->toHaveCount(1);
});

it('can create an asset from a source', function () {
    $volume = Volume::factory()->create();
    $asset = Asset::factory()
        ->volume($volume->handle)
        ->source(__DIR__ . '/../stubs/images/gray.jpg')
        ->create();

    $assets = \craft\elements\Asset::find()->volume($volume)->filename($asset->filename)->all();

    expect($assets)->toHaveCount(1);
});

it('can set the folder', function () {
    /** @var \craft\volumes\Local $volume */
    $volume = Volume::factory()->create();
    $folder = VolumeFolder::factory()->volume($volume)->create();

    Asset::factory()
        ->volume($volume->handle)
        ->folder($folder)
        ->create();

    expect((int)\craft\elements\Asset::find()
        ->volume($volume->handle)
        ->folderPath($folder->path)
        ->count())
        ->toBe(1);

    expect((int)\craft\elements\Asset::find()
        ->volume($volume->handle)
        ->folderPath('/')
        ->includeSubfolders(false)
        ->count())
        ->toBe(0);
});
