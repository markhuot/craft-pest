<?php

return [
    'omitScriptNameInUrls' => getenv('CRAFT_OMIT_SCRIPT_NAME_IN_URLS'),
    'devMode' => getenv('CRAFT_DEV_MODE'),
    'aliases' => [
        '@templates' => getenv('CRAFT_TEMPLATES_PATH'),
    ],
];
