<?php

namespace markhuot\craftpest\test;

use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\traits\DatabaseAssertions;
use markhuot\craftpest\web\TestableResponse;

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

        // Load and run Craft
        /** @var \craft\web\Application $app */
        $app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php';

        $app->projectConfig->writeYamlAutomatically = false;

        return $app;
    }

    function get($uri): TestableResponse
    {
        return (new RequestBuilder('get', $uri))->send();
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
