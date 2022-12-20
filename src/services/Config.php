<?php

namespace craft\services;

use craft\config\BaseConfig;
use markhuot\craftpest\web\Application;

class Config extends \markhuot\craftpest\overrides\Config
{
    public function getConfigFromFile(string $filename): array
    {
        $original = parent::getConfigFromFile($filename);

        if (!is_array($original) || $filename !== 'app.web') {
            return collect($original)->toArray();
        }

        return array_merge($original, require __DIR__ . '/../config/app.web.php');
    }
}
