<?php

namespace craft\services;

use craft\config\BaseConfig;
use markhuot\craftpest\web\Application;

class Config extends \markhuot\craftpest\overrides\Config
{
    public function getConfigFromFile(string $filename): array
    {
        $overrides = [];
        $original = parent::getConfigFromFile($filename);

        if ($filename === 'app.web') {
            $overrides = require __DIR__ . '/../config/app.web.php';
        }

        return array_merge($original, $overrides);
    }
}
