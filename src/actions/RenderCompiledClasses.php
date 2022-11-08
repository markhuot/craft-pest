<?php

namespace markhuot\craftpest\actions;

class RenderCompiledClasses
{
    function handle()
    {
        $template = file_get_contents(__DIR__ . '/../../stubs/compiled_classes/FactoryFields.twig');
        $compiledClass = \Craft::$app->view->renderString($template, [
            'fields' => \Craft::$app->fields->getAllFields(),
        ]);
        file_put_contents(\Craft::getAlias('@storage') . '/runtime/compiled_classes/FactoryFields.php', $compiledClass);
    }
}
