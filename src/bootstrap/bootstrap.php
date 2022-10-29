<?php

use craft\services\Config;

// Define path constants
define('CRAFT_BASE_PATH', getcwd());
define('CRAFT_VENDOR_PATH', CRAFT_BASE_PATH . '/vendor');
define('YII_ENABLE_ERROR_HANDLER', false);

// Load dotenv? 5.x vs 3.x vs 2.x
if (file_exists(CRAFT_BASE_PATH . '/.env')) {
    if (method_exists('\Dotenv\Dotenv', 'createUnsafeImmutable')) {
        /** @phpstan-ignore-next-line */
        \Dotenv\Dotenv::createUnsafeImmutable(CRAFT_BASE_PATH)->safeLoad();
    } elseif (method_exists('\Dotenv\Dotenv', 'create')) {
        /** @phpstan-ignore-next-line */
        \Dotenv\Dotenv::create(CRAFT_BASE_PATH)->load();
    } else {
        /** @phpstan-ignore-next-line */
        (new \Dotenv\Dotenv(CRAFT_BASE_PATH))->load();
    }
}

// Define additional PHP constants
// (see https://craftcms.com/docs/3.x/config/#php-constants)
define('CRAFT_ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

// Setting this causes Yii to skip calls to `exit()` and instead throw a catchable exception
// in place of `exit()`. Setting this allows us to handle the response from a `->get()` call
// uniquely in testing. We don't actually want to write to the screen and `exit()` in test.
// Instead we'll catch that exception and return the response to the tester. That's possible
// because this ENV disables `exit()` and gives us that catchable exception.
define('YII_ENV_TEST', true);

// Since we're mostly making Http requests via the `\Craft::$app` we want to simulate
// a web request in our bootstrap. Without these, the script filename will be used such
// as ./vendor/bin/pest, which will mess up Craft's internal routing. Setting these
// to dummy values allows the inital bootstrap to appear as if it's a web request.
//
// Note: these are, basically, ignored on subsequent `->get()` requests because we build
// a whole new `Request` object manually within the `http\requests\WebRequest` class.
// You probably don't want to change these. You should probably be looking in `WebRequest`
// instead.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = 'index.php';

/** @var \craft\web\Application $app */
// Load and run Craft. We have two ways we can do this. The safe way or the flexible way.
//
// First, the safe way allows us to just lean on Craft and let it do its thing. The problem
// here is that there are no hooks for us to get in to the bootstrapping lifecycle and
// therefore no way for us to inject customizations. Specifically, we need some hooks in
// to the Application boot. Normally Craft is bootstrapped from nothing on every request.
// We don't do that in testing, because Yii uses too many singletons and completely dies
// when you try to `createObject(Application)` multiple times. So, unfortunately, this
// very nice, very clean, approach is out.
// $app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php';
//
// Instead, we're going to lean on Composer's PSR-4 lazy autoloading and insert a class in
// to memory before it has a chance to load itself.
// In order to make this work we read in the default services\Config class and remap the
// existing namespace to a custom "overrides" namespace. Then we can make a new class that
// extends the override and we have full capabilities to proxy whatever methods we want
// on the original class.
//
// This alternative approach is similar to how Mockery mocks classes in PHP by leveraging
// the autoloader. The neat trick we add here is mucking around with namespaces allows us
// to extend the original class instead of just blanking it out.
//
// Note: I'm only okay with this because we're doing it in Test. I would _never_ do this
// in a production environment with code that serves content to users.
$originalConfig = file_get_contents(CRAFT_VENDOR_PATH . '/craftcms/cms/src/services/Config.php');
$originalConfig = preg_replace('/^<\?php/', '', $originalConfig);
$originalConfig = preg_replace('/^namespace.*$/m', 'namespace markhuot\\craftpest\\overrides;', $originalConfig);
eval($originalConfig);
include __DIR__ . '/../services/Config.php';
$app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php';

$app->projectConfig->writeYamlAutomatically = false;

return $app;
