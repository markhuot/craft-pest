<?php

return [
    'modules' => [
        'pest-module-test' => \markhuot\craftpest\modules\test\Module::class,
    ],
    'bootstrap' => [
        'pest-module-test',
    ]
];
