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
            if (is_a($volume->fs, \craft\fs\Local::class)) {                              // @phpstan-ignore-line
                FileHelper::removeDirectory($volume->fs->getRootPath());                        // @phpstan-ignore-line
            }
        } else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
            /** @var \craft\base\VolumeInterface $volume */
            if (is_a($volume, \craft\volumes\Local::class)) {                             // @phpstan-ignore-line
                FileHelper::removeDirectory($volume->getRootPath());                            // @phpstan-ignore-line
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
            $fileSystem = new \craft\fs\Local();                                                                  // @phpstan-ignore-line
            $fileSystem->name = $definition['name'] . ' FS';                                                      // @phpstan-ignore-line
            $fileSystem->handle = $definition['handle'] . 'Fs';                                                   // @phpstan-ignore-line
            $fileSystem->path = \Craft::getAlias('@storage') . '/volumes/' . $definition['handle'] . '/';    // @phpstan-ignore-line
            \Craft::$app->fs->saveFilesystem($fileSystem);                                                        // @phpstan-ignore-line

            $definition['fsHandle'] = $fileSystem->handle;                                                        // @phpstan-ignore-line
        } else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
            $definition['path'] = \Craft::getAlias('@storage') . '/volumes/' . $definition['handle'] . '/'; // @phpstan-ignore-line
        }

        return $definition;
    }
}
