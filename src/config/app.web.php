<?php

use markhuot\craftpest\web\Application;

return [
    'class' => Application::class,

    // For Craft 4.3+ we need to disable Yaml generation so that Factories don't
    // save their ephemeral schemas out to project.yaml. A factory for a field should
    // be torn down after the test and leave no trace so we need to make sure that
    // project-config is _not_ updated.
    'components' => [
        'projectConfig' => function() {
            $config = craft\helpers\App::projectConfigConfig();
            $config['writeYamlAutomatically'] = false;
            return Craft::createObject($config);
        },
    ],

    // I dont want to force enable this here because there's a lot of logic in
    // the craft\web\Application::bootstrapDebug() that handles some edge cases. All
    // that custom logic is enabled with the `devMode` setting.
    // 'modules' => [
    //     'debug' => [
    //         'class' => 'yii\debug\Module',
    //         'allowedIPs' => ['127.0.0.1', '::1'],
    //     ],
    // ],
    // 'bootstrap' => [
    //     'debug',
    // ],
];
