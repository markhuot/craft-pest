<?php

return [
    'isSystemLive' => true,
    'devMode' => true,
    'omitScriptNameInUrls' => getenv('CRAFT_OMIT_SCRIPT_NAME_IN_URLS'),
    'aliases' => [
        '@templates' => getenv('CRAFT_TEMPLATES_PATH'),
    ],
];
