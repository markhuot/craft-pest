<?php

use markhuot\craftpest\web\Application;

return [
    'class' => Application::class,

    // I dont want to force enable this here because there's a lot of logic in
    // the craft\debug\Module::bootstrap() that handles some edge cases. All
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
