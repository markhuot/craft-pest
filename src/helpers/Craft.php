<?php

namespace markhuot\craftpest\helpers\craft;

use craft\helpers\FileHelper;
use function markhuot\craftpest\helpers\base\version_greater_than_or_equal_to;

/**
 * @codeCoverageIgnore
 */
if (!function_exists('volumeDeleteFileAtPath')) {
    function volumeDeleteFileAtPath($volume, string $path)
    {
        if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
            /** @var \craft\models\Volume $volume */
            // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
            $volume->getFs()->deleteFile($path);
        } else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
            /** @var \craft\base\VolumeInterface $volume */
            // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
            $volume->deleteFile($path);
        }
    }
}

/**
 * @codeCoverageIgnore
 */
if (!function_exists('volumeDeleteRootDirectory')) {
    function volumeDeleteRootDirectory($volume)
    {
        if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
            /** @var \craft\models\Volume $volume */
            if (is_a($volume->fs, \craft\fs\Local::class)) {
                FileHelper::removeDirectory($volume->fs->getRootPath());
            }
        } else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
            /** @var \craft\base\VolumeInterface $volume */
            if (is_a($volume, \craft\volumes\Local::class)) {
                FileHelper::removeDirectory($volume->getRootPath());
            }
        }
    }
}

/**
 * @codeCoverageIgnore
 */
if (!function_exists('createVolume')) {
    function createVolume()
    {
        if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
            // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
            return new \craft\models\Volume;
        } else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
            /** @var \craft\base\VolumeInterface $volume */
            // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
            return new \craft\volumes\Local;
        }
    }
}

/**
 * @codeCoverageIgnore
 */
if (!function_exists('volumeDefinition')) {
    function volumeDefinition(array $definition=[])
    {
        if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
            $fileSystem = new \craft\fs\Local();
            $fileSystem->name = $definition['name'] . ' FS';
            $fileSystem->handle = $definition['handle'] . 'Fs';
            $fileSystem->path = \Craft::getAlias('@storage') . '/volumes/' . $definition['handle'] . '/';
            \Craft::$app->fs->saveFilesystem($fileSystem);

            $definition['fsHandle'] = $fileSystem->handle;
        } else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
            $definition['path'] = \Craft::getAlias('@storage') . '/volumes/' . $definition['handle'] . '/';
        }

        return $definition;
    }
}
