<?php

// Load shared bootstrap
require __DIR__.'/../bootstrap.php';

// Load and run Craft
/** @var craft\console\Application $app */
$app = require CRAFT_VENDOR_PATH.'/craftcms/cms/bootstrap/console.php';

$fs = $app->fs->getFilesystemByHandle('local');
if (! $fs) {
    $fs = $app->fs->createFilesystem([
        'type' => \craft\fs\Local::class,
        'name' => 'Local',
        'handle' => 'local',
        'hasUrls' => true,
        'url' => 'http://localhost:8080/volumes/local/',
        'settings' => ['path' => CRAFT_BASE_PATH.'/web/volumes/local'],
    ]);
    $result = $app->fs->saveFilesystem($fs);
}

$volume = $app->volumes->getVolumeByHandle('local');
if (! $volume) {
    $volume = new \craft\models\Volume();
    $volume->name = 'Local';
    $volume->handle = 'local';
    $volume->fs = $fs;
    $app->volumes->saveVolume($volume);
}

$app->run();
