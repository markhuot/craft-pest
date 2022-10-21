<?php

namespace markhuot\craftpest\test;

use Composer\Plugin\PluginManager;
use craft\services\Plugins;
use craft\web\Application;
use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\traits\DatabaseAssertions;
use markhuot\craftpest\web\TestableResponse;
use yii\base\Event;

class TestCase extends \PHPUnit\Framework\TestCase {

    use ActingAs, DatabaseAssertions;

    protected function setUp(): void
    {
        $this->createApplication();

        $this->callTraits('setUp');
    }

    protected function tearDown(): void
    {
        $this->callTraits('tearDown');
    }

    protected function callTraits($prefix)
    {
        // Get traits added to the base TestCase, this actual file
        $reflect = new \ReflectionClass($this);
        $traits = $reflect->getParentClass()->getTraits();

        // Get traits added via Pest's `uses()` logic
        $traits = array_merge($traits, $reflect->getTraits());

        foreach ($traits as $trait) {
            $method = $prefix . $trait->getShortName();
            if ($trait->hasMethod($method)) {
                $this->{$method}();
            }
        }
    }

    public function createApplication()
    {
        if ($this->needsRequireStatements()) {
            $this->requireCraft();
        }

        return \Craft::$app;
    }

    protected function needsRequireStatements()
    {
        return !defined('CRAFT_BASE_PATH');
    }

    protected function requireCraft()
    {
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

        // Set this so we can catch calls to `\Craft::$app->exit()` and handle them
        // uniquely in testing. We don't actually want to `exit()` in test.
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

        // Craft bootstraps the request _very_ early in the lifecycle so plugins, modules, etc can
        // all have guranteed access to things like $request->isConsoleRequest. Unfortunately the 
        // default functionality looks at `PHP_SAPI === 'cli'`, which will be true in our test 
        // environment. Because the default request looks like a console request any plugin bootstrap
        // will then register its console controller namespace. For _most_ prople this is not what we
        // want. Most people are typically testing web requests. So, for the majority we reset the
        // isConsoleRequest flag back to web. Eventually this should probably be configurable.
        Event::on(Plugins::class, Plugins::EVENT_BEFORE_LOAD_PLUGINS, function() {
            \Craft::$app->request->setIsConsoleRequest(false);
        });

        // Load and run Craft
        /** @var \craft\web\Application $app */
        $app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php';

        $app->projectConfig->writeYamlAutomatically = false;

        return $app;
    }

    function get(...$args): TestableResponse
    {
        //return Pest::getInstance()->http->get(...$args);
        return (new RequestBuilder('get', ...$args))->send();
    }

    function post(...$args): TestableResponse
    {
        return (new RequestBuilder('post', ...$args))->send();
    }

    function http(string $method, string $uri): RequestBuilder
    {
        return new RequestBuilder($method, $uri);
    }

    public function factory(string $class)
    {
        return $class::factory();
    }

}
