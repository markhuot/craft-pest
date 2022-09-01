<?php

namespace markhuot\craftpest\helpers\craft;

use function markhuot\craftpest\helpers\base\version_greater_than_or_equal_to;

/**
 * @codeCoverageIgnore
 */
if (!function_exists('volumeDeleteFileAtPath')) {
    function volumeDeleteFileAtPath(\craft\base\VolumeInterface $volume, string $path)
    {
        if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
            // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
            $volume->getFs()->deleteFile($path);
        } else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
            // @phpstan-ignore-next-line Ignored because one of these will fail based on the installed version of Craft
            $volume->deleteFile($path);
        }
    }
}
