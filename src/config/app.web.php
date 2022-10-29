<?php

use markhuot\craftpest\web\Application;

return [
    'class' => Application::class,
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['127.0.0.1', '::1'],
        ],
    ],
    'bootstrap' => [
        'debug',
    ],
];
