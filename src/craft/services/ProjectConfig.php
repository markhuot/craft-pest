<?php

namespace craft\services;

class ProjectConfig extends \markhuot\craftpest\overrides\ProjectConfig
{
    public function saveModifiedConfigData(?bool $writeExternalConfig = null): void {
        // no-op since we don't ever want to save config data back to the DB in a test
    }
}