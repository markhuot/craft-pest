<?php

use markhuot\craftpest\web\Application;

return [
    'class' => Application::class,

    // Mark, is this the right place to enable the Module for internal tests?
    'modules' => [
        'pest-module-test' => \markhuot\craftpest\modules\test\Module::class,
    ],
    'bootstrap' => [
        'pest-module-test',
    ]
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
