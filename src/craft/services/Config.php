<?php

namespace craft\services;

use craft\config\BaseConfig;

class Config extends \markhuot\craftpest\overrides\Config
{
    /**
     * @return array|BaseConfig
     */
    public function getConfigFromFile(string $filename): array|BaseConfig
    {
        $original = parent::getConfigFromFile($filename);

        if (!is_array($original) || $filename !== 'app.web') {
            return $original;
        }

        return array_merge($original, require __DIR__ . '/../../config/app.web.php');
    }
}
