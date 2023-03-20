<?php

namespace markhuot\craftpest\actions;

use craft\db\Table;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;

class RenderCompiledClasses
{
    function handle($forceRecreate=false)
    {
        $contentService = \Craft::$app->getContent();
        $originalContentTable = $contentService->contentTable;
        $originalFieldColumnPrefix = $contentService->fieldColumnPrefix;
        $originalFieldContext = $contentService->fieldContext;
        $contentService->contentTable = Table::CONTENT;
        $contentService->fieldColumnPrefix = 'field_';
        $contentService->fieldContext = 'global';

        $this->render($forceRecreate);

        $contentService->contentTable = $originalContentTable;
        $contentService->fieldColumnPrefix = $originalFieldColumnPrefix;
        $contentService->fieldContext = $originalFieldContext;

        return true;
    }

    protected function render(bool $forceRecreate)
    {
        $storedFieldVersion = \Craft::$app->fields->getFieldVersion();
        $compiledClassesPath = __DIR__ . '/../storage/';
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
    }

    protected function cleanupOldMixins(string $except=null)
    {
        $compiledClassesPath = __DIR__ . '/../storage/';

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
