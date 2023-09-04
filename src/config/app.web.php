<?php

use markhuot\craftpest\web\Application;

return [
    'class' => Application::class,

    'components' => [
        'session' => function() {
            $config = craft\helpers\App::sessionConfig();
            $config['class'] = markhuot\craftpest\web\Session::class;
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
