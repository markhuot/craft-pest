<?php

namespace markhuot\craftpest\actions;

use craft\helpers\StringHelper;

class RenderCompiledClasses
{
    function handle($forceRecreate=false)
    {
        $storedFieldVersion = \Craft::$app->fields->getFieldVersion();
        $compiledClassesPath = \Craft::$app->getPath()->getCompiledClassesPath();
        $fieldVersionExists = $storedFieldVersion !== null;
        if (!$fieldVersionExists) {
            $storedFieldVersion = StringHelper::randomString(12);
        }

        $compiledClassPath = $compiledClassesPath . DIRECTORY_SEPARATOR . 'FactoryFields_' . $storedFieldVersion . '.php';

        if (file_exists($compiledClassPath) && !$forceRecreate) {
            return false;
        }

        $template = file_get_contents(__DIR__ . '/../../stubs/compiled_classes/FactoryFields.twig');

        $compiledClass = \Craft::$app->view->renderString($template, [
            'fields' => \Craft::$app->fields->getAllFields(),
        ]);

        file_put_contents($compiledClassPath, $compiledClass);

        return true;
    }
}
