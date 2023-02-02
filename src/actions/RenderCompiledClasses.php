<?php

namespace markhuot\craftpest\actions;

use craft\helpers\FileHelper;
use craft\helpers\StringHelper;

class RenderCompiledClasses
{
    function handle($forceRecreate=false)
    {
        $storedFieldVersion = \Craft::$app->fields->getFieldVersion();
        $compiledClassesPath = \Craft::$app->getPath()->getVendorPath() . '/markhuot/craft-pest/src/storage/';
        $fieldVersionExists = $storedFieldVersion !== null;
        if (!$fieldVersionExists) {
            $storedFieldVersion = StringHelper::randomString(12);
        }

        $compiledClassPath = $compiledClassesPath . DIRECTORY_SEPARATOR . 'FactoryFields_'.$storedFieldVersion.'.php';

        if (file_exists($compiledClassPath) && !$forceRecreate) {
            return false;
        }

        $this->cleanupOldMixins('FactoryFields_' . $storedFieldVersion . '.php');

        $template = file_get_contents(__DIR__ . '/../../stubs/compiled_classes/FactoryFields.twig');

        $compiledClass = \Craft::$app->view->renderString($template, [
            'fields' => \Craft::$app->fields->getAllFields(false),
        ]);

        file_put_contents($compiledClassPath, $compiledClass);

        return true;
    }

    protected function cleanupOldMixins(string $except=null)
    {
        $compiledClassesPath = \Craft::$app->getPath()->getVendorPath() . '/markhuot/craft-pest/src/storage/';

        FileHelper::clearDirectory($compiledClassesPath, [
            'filter' => function(string $path) use ($except): bool {
                $b = basename($path);
                return (
                    str_starts_with($b, 'FactoryFields') &&
                    ($except === null || $b !== $except)
                );
            },
        ]);
    }
}
